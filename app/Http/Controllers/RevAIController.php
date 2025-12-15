<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RevAIController extends Controller
{
    private string $base;
    private string $token;

    public function __construct()
    {
        $this->base  = rtrim(config('services.revai.base', env('REVAI_BASE_URL', 'https://api.rev.ai/speechtotext/v1')), '/');
        $this->token = (string) config('services.revai.token', env('REVAI_ACCESS_TOKEN'));
    }

    // 1) Faylni yuborib job yaratish (multipart)
    public function transcribe(Request $request)
    {
        $data = $request->validate([
            'audio'    => 'required|file',
            'language' => 'nullable|string',
        ]);

        $file     = $request->file('audio');
        $origName = $file->getClientOriginalName();
        $mime     = $file->getMimeType() ?: 'application/octet-stream';
        $lang     = $data['language'] ?? 'ja';  // ← yaponcha default
        logger()->error($data['language']);

        $stream = fopen($file->getRealPath(), 'r');
        if ($stream === false) {
            return response()->json(['error' => 'Cannot open uploaded file'], 400);
        }

        // MUHIM: 'options' maydonini JSON STRING qilib yuboramiz
        $options = json_encode([
            'language' => $lang,
            // ixtiyoriy: 'remove_disfluencies' => true,
            // ixtiyoriy: 'skip_diarization'   => true,
        ]);

        $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . trim($this->token),
                ])
                ->attach('media', $stream, $origName, ['Content-Type' => $mime])
                ->post($this->base . '/jobs', [
                    'options' => $options,   // ← shu yerda tilni beramiz
                ]);

        if (!$resp->ok()) {
            return response()->json([
                'error'  => 'RevAI submit failed',
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ], 502);
        }

        return response()->json([
            'id'     => $resp->json('id'),
            'status' => $resp->json('status'),
            'name'   => $origName,
            'lang'   => $lang,
        ]);
    }

    // 1b) URL orqali job yaratish (ixtiyoriy)
    public function transcribeFromUrl(Request $request)
    {
        $data = $request->validate([
            'media_url' => 'required|url',
            'language'  => 'nullable|string',
        ]);
        $lang = $data['language'] ?? 'ja';

        $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . trim($this->token),
                    'Content-Type'  => 'application/json',
                ])->post($this->base . '/jobs', [
                    'source_config' => ['url' => $data['media_url']],
                    'language'      => $lang,
                ]);

        if (!$resp->ok()) {
            return response()->json([
                'error'  => 'RevAI submit failed',
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ], 502);
        }

        return response()->json([
            'id'     => $resp->json('id'),
            'status' => $resp->json('status'),
        ]);
    }

    // 2) Holatni tekshirish
    public function status(string $id)
    {
        $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . trim($this->token),
                ])->get($this->base . "/jobs/$id");

        if (!$resp->ok()) {
            return response()->json([
                'error'  => 'RevAI status failed',
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ], 502);
        }

        return response()->json($resp->json());
    }

    // 3) Matnni olish: plain text formatga o'giramiz (frontend uchun qulay)
    public function textPlain(string $id)
    {
         $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($this->token),
                'Accept'        => 'application/vnd.rev.transcript.v1.0+json',
            ])->get($this->base . "/jobs/$id/transcript");

        if (!$resp->ok()) {
            return response()->json([
                'error'  => 'RevAI transcript fetch failed',
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ], 502);
        }

        

        $data = $resp->json();  

        // 2) JSON → text (Rev AI JSON: monologues[].elements[] bo'ladi)
        $text = '';
        if (is_array($data) && isset($data['monologues']) && is_array($data['monologues'])) {
            foreach ($data['monologues'] as $mono) {
                if (!isset($mono['elements']) || !is_array($mono['elements'])) continue;
                foreach ($mono['elements'] as $el) {
                    $val  = $el['value'] ?? '';
                    $type = $el['type']  ?? 'text';
                    if ($type === 'punct') {
                        // Punktuatsiya oldidan bo'shliqni olib tashlab qo'yamiz
                        $text = rtrim($text) . $val . ' ';
                    } else {
                        $text .= $val . ' ';
                    }
                }
                $text = rtrim($text) . PHP_EOL . PHP_EOL; // monologlar orasiga bo'sh qator
            }
            $text = trim($text);
        }

        // 3) Agar baribir bo'sh bo'lib qolsa, fallback sifatida plain-textni yana bir marta urinib ko'ramiz
        if ($text === '') {
            $respTxt = Http::withHeaders([
                            'Authorization' => 'Bearer ' . trim($this->token),
                            'Accept'        => 'text/plain',
                        ])->get($this->base . "/jobs/$id/transcript");
            if ($respTxt->ok()) {
                $text = trim($respTxt->body() ?? '');
            }
        }

        return response()->json(['text' => $text]);
    }
}
