<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaClient
{
    public function __construct(
        private string $baseUrl = '',
        private string $model = '',
    ) {
        $this->baseUrl = config('services.ollama.base_url');
        $this->model   = config('services.ollama.model', 'qwen2.5:7b-instruct');
    }

    /**
     * /api/generate orqali chaqiradi (stream=false).
     */
    public function generate(string $prompt, array $options = []): string
    {
        $payload = [
            'model'   => $this->model,
            'prompt'  => $prompt,
            'stream'  => false,
            'options' => array_merge([
                'temperature' => 0.2,   // deterministikroq
                'num_ctx'     => 2048,  // CPU uchun yetarli kontekst
                // 'num_thread' => 8,    // ixtiyoriy: CPU yadro soniga qarab
            ], $options),
        ];

        $res = Http::baseUrl($this->baseUrl)
            ->timeout(60)               // kerak bo'lsa oshiring
            ->acceptJson()
            ->post('/api/generate', $payload);

        if (!$res->ok()) {
            throw new \RuntimeException('Ollama HTTP status: '.$res->status().' '.$res->body());
        }

        $body = $res->json();
        // Ollama generate javob formati: { "response": "...", "done": true, ...}
        $text = $body['response'] ?? '';

        if (!is_string($text) || $text === '') {
            throw new \RuntimeException('Bo\'sh yoki noto\'g\'ri model javobi');
        }
        return $text;
    }
}
