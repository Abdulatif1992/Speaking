<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OllamaClient;
use Illuminate\Validation\ValidationException;
use Throwable;

class QaScoreController extends Controller
{
    public function __construct(private OllamaClient $ollama) {}
    

    public function score(Request $request)
    {
        $data = $request->validate([
            'question' => ['required','string','max:20000'],
            'answer'   => ['required','string','max:20000'],
        ]);

        // Qat’iy JSON-only prompt (uzunligini xohishga ko'ra sozlang)
        $prompt = <<<EOT
                    You are an evaluator. Your task is to assess the quality of an answer given to a question.
                    Instructions:
                    - Evaluate the provided Question and Answer according to these four criteria (each 0–10):
                    1) Accuracy — How factually correct is the answer?
                    2) Relevance — How well does the answer address the question?
                    3) Completeness — Does the answer fully cover the important points required by the question?
                    4) Fluency/Clarity — How clear, well-structured, and grammatically correct is the answer?

                    - Compute the Final Score as the arithmetic mean:
                    Final Score = (Accuracy + Relevance + Completeness + Fluency/Clarity) / 4

                    Output requirements:
                    1. RETURN VALID JSON ONLY (no extra text). The JSON must include the keys exactly as below:
                    {
                    "accuracy": number,                     // 0-10 (integer or float)
                    "accuracy_explanation": string,
                    "relevance": number,                    // 0-10
                    "relevance_explanation": string,
                    "completeness": number,                 // 0-10
                    "completeness_explanation": string,
                    "fluency": number,                      // 0-10
                    "fluency_explanation": string,
                    "final_score": number,                  // computed mean, round to 2 decimals
                    "human_readable": string                // optional nicely formatted summary
                    }

                    2. All numeric scores must be between 0 and 10. Use integers when possible.
                    3. Each "*_explanation*" must briefly (1-2 sentences) justify the numeric score. 
                    Example: if "accuracy" is 8, explain why it deserved 8 (which facts were correct / missing / slightly incorrect).
                    4. final_score must equal the arithmetic mean of the four criterion scores. Round to 2 decimal places.
                    5. Do NOT include any other fields, comments, or surrounding text. If you cannot evaluate, return {"error":"explain reason"}.

                    Now evaluate the following pair:

                    Question: {$data['question']}
                    Answer: {$data['answer']}
                    EOT;

        try {
            $raw = $this->ollama->generate($prompt);
            // Model matn ko'pincha JSON-string bo'ladi: {"score":7,"rationale_uz":"..."}
            $parsed = $this->extractJson($raw);

            if (!isset($parsed['score']) || !is_numeric($parsed['score'])) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Model invalid JSON qaytardi',
                    'raw' => $raw,
                ], 502);
            }

            // 1–10 oralig'iga siqib qo'yish (himoya)
            $score = max(1, min(10, (int)$parsed['score']));
            return response()->json([
                'ok' => true,
                'score' => $score,
                'rationale_uz' => $parsed['rationale_uz'] ?? null,
                'model' => config('services.ollama.model'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Ollama bilan aloqa xatosi',
                'details' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 502);
        }
    }



    /**
     * Model javobidan JSON obyektni ajratib olish (fallback).
     */
    private function extractJson(string $text): array
    {
        // Tez usul: birinchi { ... } blokni olish
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $j = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
                return $j;
            }
        }
        // To'g'ridan-to'g'ri decode sinab ko'rish
        $j = json_decode(trim($text), true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($j)) ? $j : [];
    }
}
