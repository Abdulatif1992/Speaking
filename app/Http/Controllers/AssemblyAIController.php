<?php
namespace App\Http\Controllers;
mb_internal_encoding('UTF-8');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AssemblyAIController extends Controller
{
    private string $base;
    private string $key;

    public function __construct()
    {
        $this->base = rtrim(config('services.assemblyai.base', env('AAI_BASE_URL', 'https://api.assemblyai.com/v2')), '/');
        $this->key  = config('services.assemblyai.key', env('AAI_API_KEY'));
    }

    // 1) Faylni AAI'ga upload qilish
    private function uploadToAAI(string $binary): string
    {
        $resp = Http::asJson(false) // <— MUHIM: hech qaerda json_encode bo'lmasin
            ->withHeaders([
                'Authorization' => $this->key,
                'Accept'        => 'application/json',
            ])
            ->withBody($binary, 'application/octet-stream') // <— xom binar body
            ->post($this->base . '/upload');

        if (!$resp->ok()) {
            abort(502, 'AAI upload failed: ' . ($resp->body() ?: $resp->status()));
        }

        $uploadUrl = $resp->json('upload_url');
        if (!is_string($uploadUrl) || $uploadUrl === '') {
            abort(502, 'AAI upload returned invalid upload_url');
        }
        return $uploadUrl;
    }

    // 2) Transkripsiya yaratish
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file',
            'language_code' => 'nullable|string', // 'ja'
        ]);
        

        $file = $request->file('audio');
        $binary = file_get_contents($file->getRealPath());
        
        // 1) Upload
        $uploadUrl = $this->uploadToAAI($binary);

        // transcript yaratish (speaker diarization va boshqalar ixtiyoriy)
        $payload = [
            'audio_url'      => $uploadUrl,
            'language_code'  => $request->input('language_code', 'ja'),
            'speaker_labels' => false,
        ];

        // JSON ni o'zimiz enkod qilamiz (UTF-8 muammosiz)
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            return response()->json([
                'error' => 'json_encode failed',
                'msg'   => json_last_error_msg(),
                'data'  => $payload,
            ], 500);
        }

        // 🔧 MUHIM: bazaviy URL va kalitni QATTIQ tozalaymiz
        $key  = preg_replace('~[\p{C}\s]+~u', '', (string)$this->key);   // barcha boshqaruv/bo'sh belgilardan tozalash
        $base = preg_replace('~[\p{C}\s]+$~u', '', (string)$this->base);  // oxiri-dagi \r \n \t va hk. ni olib tashlash
        $base = rtrim($base, '/');
        $url  = $base . '/transcript'; // final URL

        // 🔎 ixtiyoriy: tez “ping” (GET bo'lsa 405 kelishi kerak; 404 kelsa URL buzilgan)
        $ping = Http::asJson(false)->withHeaders(['authorization'=>$key])->get($url);
        logger()->info('PING', ['status'=>$ping->status(), 'len_base'=>strlen($base), 'url'=>$url]);

        $resp = Http::asJson(false)
            ->withHeaders([
                'Authorization' => $key,              // faqat kalitning o'zi (Bearer so'zi kerak emas)
                'Accept'        => 'application/json'
            ])
            ->withBody($json, 'application/json')     // JSON body'ni aniq beramiz
            ->post($base . '/transcript');

        if (!$resp->ok()) {
            // 502 o‘rniga aynan nima xato bo‘layotganini ko‘rib olaylik:
            return response()->json([
                'error'  => 'AAI transcript failed',
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ], 502);
        }

        return response()->json(['id' => $resp->json('id')]);
    }

    // 3) Holatni tekshirish (polling)
    public function status(string $id)
    {
        $resp = Http::withHeaders([
            'Authorization' => $this->key,
        ])->get($this->base . '/transcript/' . $id);

        if (!$resp->ok()) abort(502, 'AAI status failed: '.$resp->body());

        // completed bo'lsa 'text' mavjud, speaker_labels yoqsangiz 'utterances' ham bo'ladi
        return response()->json([
            'status' => $resp->json('status'),
            'text'   => $resp->json('text'),
            'error'  => $resp->json('error'),
            'utterances' => $resp->json('utterances'), // diarization bo'lsa
        ]);
    }
}