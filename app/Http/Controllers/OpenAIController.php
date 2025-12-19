<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Services\ExcelSurveyReader;

class OpenAIController extends Controller
{
    public function score(Request $request, ExcelSurveyReader $reader)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);
        

        $filePath  = storage_path('app/questions.xlsx');
        $sheetName = 'N52';

        $data = $reader->read($filePath, $sheetName);

        // Normalizatsiya (bo'sh joy va satr farqlaridan himoya)
        $incomingQuestion = trim(preg_replace('/\s+/u', ' ', $request->input('question')));

        $matched = collect($data)->first(function ($item) use ($incomingQuestion) {
            $q = trim(preg_replace('/\s+/u', ' ', (string)($item['question'] ?? '')));
            return $q === $incomingQuestion;
        });

        if (!$matched) {
            dd("mos savol topilmadi");
        }

        // Endi $matched ichida shu savolga tegishli javoblar bor:
        $answers10 = $matched['answers']['10_point'] ?? [];
        $answers5  = $matched['answers']['5_point'] ?? [];

        $predictedGrade = $this->predictOpenAI($incomingQuestion, $answers10, $answers5, $request->input('answer'));       
        
        return response()->json([
            "ok" => true,
            "grade" => $predictedGrade['score'],
            "is_correct" => $predictedGrade['is_correct'],
        ]);
    }

    static function predictOpenAI($question, $answers10, $answers5, $userAnswer){
        $bank = [];
        $id = 1;
        // 10 ballik javoblar
        foreach ($answers10 as $answer) {
            $bank[] = [
                'id'     => $id++,
                'answer' => $answer,
                'score'  => 10,
            ];
        }

        // 5 ballik javoblar
        foreach ($answers5 as $answer) {
            $bank[] = [
                'id'     => $id++,
                'answer' => $answer,
                'score'  => 5,
            ];
        }

        $systemPrompt = <<<SYS
            You are a strict grading engine.
            You will receive:
            - a question
            - a student's answer
            - an answer bank: examples with numeric scores (0-10)

            Task:
            1) Find the single most similar answer from the answer bank to the student's answer. Return its id as matched_id.
            2) Decide whether the student's answer is actually similar enough to that bank answer:
            - If it is similar enough, set score equal to the matched bank score.
            - If it is NOT similar enough (the answer does not match any bank example), set matched_id to "NONE" and estimate the score yourself.
            3) When estimating the score yourself, consider:
            - Relevance to the question (most important).
            - Naturalness and correctness.
            - Politeness: more respectful/polite Japanese (丁寧語/敬語) should score slightly higher than a plain neutral answer.
                Very casual/overly informal style should reduce the score slightly.
            Return ONLY valid JSON. No extra text.
            Keys: predicted_score.
            predicted_score: number 0-10.
            SYS;
            

        $userText = json_encode([
            "question" => $question,
            "student_answer" => $userAnswer,
            "answer_bank" => $bank,
        ], JSON_UNESCAPED_UNICODE);


        $client = new Client();

        $res = $client->post("https://api.openai.com/v1/responses", [
            "headers" => [
                "Authorization" => "Bearer " . env("OPENAI_API_KEY"),
                "Content-Type"  => "application/json",
            ],
            "json" => [
                "model" => env("OPENAI_MODEL", "gpt-5-mini-2025-08-07"),
                "input" => [
                    [
                    "role" => "system",
                    "content" => [[ "type" => "input_text", "text" => $systemPrompt ]],
                    ],
                    [
                    "role" => "user",
                    "content" => [[ "type" => "input_text", "text" => $userText ]],
                    ],
                ],
                "text" => [
                    "format" => ["type" => "json_object"],
                    "verbosity" => "low",
                ],
                "reasoning" => ["effort" => "minimal"],
                "max_output_tokens" => 800,

            ],
        ]);

        $body = json_decode($res->getBody(), true);
        $outText = null;

        // Responses API: output -> content -> (type=output_text) -> text
        foreach (($body['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $c) {
                if (($c['type'] ?? '') === 'output_text') {
                    $outText = $c['text'] ?? null;
                    break 2;
                }
            }
        }

        // fallback (ba'zi holatlarda bo'lishi mumkin)
        $outText = $outText ?? ($body['output_text'] ?? null);

        $grade = $outText ? json_decode($outText, true) : null;
        if (!is_array($grade)) {
            $grade = ["predicted_score" => 0, "reason" => "Invalid JSON from model."];
        }

        $pred = (float)($grade['predicted_score'] ?? 0);
        $pred = max(0, min(10, $pred));

        
        return ([
            "score" => round($pred, 1),
            "is_correct" => $pred >= 7,
        ]);


    }

    public function importExcel(ExcelSurveyReader $reader)
    {
        $filePath = storage_path('app/questions.xlsx');
        $sheetName = 'N52';        
        $data = $reader->read($filePath, $sheetName);

        if (empty($data)) {
            return response()->json(['error' => 'Savollar topilmadi'], 404);
        }

        $randomQuestion = $data[array_rand($data)];
        return view('openAI.design', ['data' => $randomQuestion]);
    }

    
}
