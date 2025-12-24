<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Services\ExcelSurveyReader;
use App\Services\AnswerGraderService;
use App\Services\JapaneseReading\JapaneseReadingInterface;

class DBAndAIController extends Controller
{
    public function importExcel(ExcelSurveyReader $reader)
    {
        $filePath = storage_path('app/questions.xlsx');
        $sheetName = 'N52';        
        $data = $reader->read($filePath, $sheetName);

        if (empty($data)) {
            return response()->json(['error' => 'Savollar topilmadi'], 404);
        }

        $randomQuestion = $data[array_rand($data)];
        return view('openAI.dbandai', ['data' => $randomQuestion]);
    }

    public function score(Request $request, ExcelSurveyReader $reader, AnswerGraderService $grader, JapaneseReadingInterface $reading,){
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);
        $question = $request->input('question');
        $use_answer = $request->input('answer');

        // $question = "おにぎりは何個ありますか？";
        // $use_answer = "おにぎりは3個です";

        // Normalizatsiya (bo'sh joy va satr farqlaridan himoya)
        $incomingQuestion = trim(preg_replace('/\s+/u', ' ', $question));
        
        $filePath  = storage_path('app/questions.xlsx');
        $sheetName = 'N52';

        $data = $reader->read($filePath, $sheetName);

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

        [$score, $reason, $matched2, $sim] = $grader->grade($use_answer, $answers10, $answers5, [
            'fuzzy_threshold_10' => 0.90,
            'fuzzy_threshold_5'  => 0.80,
            'fuzzy_max_score'    => 0,
        ]);

        if($score != 0){
            return response()->json([
                "ok" => true,
                "grade" => $score,
                "is_correct" => $score >= 7,
                "from" =>   "excel",
            ]);
        }
        else{
            $predictedGrade = $this->fullPredictOpenAI($incomingQuestion, $use_answer);  
            
            return response()->json([
                "ok" => true,
                "grade" => $predictedGrade['score'],
                "is_correct" => $predictedGrade['is_correct'],
                "from" =>   "openAI",
            ]);
        }       
        
        
    }  
    
    static function fullPredictOpenAI($question, $userAnswer){
    // static function fullPredictOpenAI(){
        
        $systemPrompt = <<<SYS
            You are a strict and consistent evaluator of Japanese-language answers.

            Your task is to evaluate a Japanese ANSWER given a Japanese QUESTION.
            Evaluate the ANSWER using the four criteria below.

            Return ONLY a valid JSON object.
            Do not include explanations or extra text.

            Required JSON keys:
            - semantic_accuracy
            - grammatical_naturalness
            - politeness_register
            - clarity_effectiveness

            Each value must be a number from 0 to 10.

            ---

            Evaluation Criteria:

            1) Semantic Accuracy & Relevance (semantic_accuracy)
            Evaluate whether the answer correctly understands and addresses the intent of the question.
            Consider meaning accuracy in Japanese context and relevance.
            Score:
            - 10: Fully correct and directly answers the question
            - 7–9: Mostly correct with minor omissions or ambiguity
            - 4–6: Partially correct; important information missing or unclear
            - 1–3: Largely incorrect or misinterprets the question
            - 0: Completely incorrect or unrelated

            2) Grammatical Accuracy & Naturalness (grammatical_naturalness)
            Evaluate Japanese grammar, sentence structure, particles, conjugation, and naturalness.
            Score:
            - 10: Grammatically correct and natural, native-like
            - 7–9: Minor grammatical issues, meaning remains clear
            - 4–6: Frequent grammatical issues affecting clarity
            - 1–3: Serious grammatical problems, difficult to understand
            - 0: Largely unintelligible Japanese

            3) Politeness, Register & Situational Appropriateness (politeness_register)
            Evaluate use of polite forms (です・ます, 丁寧語, 敬語 when appropriate),
            consistency of register, and cultural appropriateness for the situation.
            Score:
            - 10: Fully appropriate politeness and register for the context
            - 7–9: Generally polite with minor inconsistencies
            - 4–6: Neutral or casual where politeness is expected
            - 1–3: Blunt or culturally inappropriate tone
            - 0: Rude, disrespectful, or offensive

            Note: A correct answer may receive a lower score if politeness or register is inappropriate.

            4) Clarity, Coherence & Communicative Effectiveness (clarity_effectiveness)
            Evaluate clarity, logical flow, structure, and ease of understanding for a Japanese reader.
            Score:
            - 10: Clear, well-structured, concise, and easy to follow
            - 7–9: Mostly clear with minor flow or structure issues
            - 4–6: Somewhat unclear or poorly structured
            - 1–3: Confusing or difficult to follow
            - 0: Not communicative or unusable

            ---

            Consistency Rules:
            - Evaluate each criterion independently.
            - Be consistent across identical inputs.
            - Do not infer information that is not explicitly stated.
            
            SYS;

        // $question = "友達の声が大きくて、勉強できません。何と言いますか。";
        // $userAnswer = "うるさいんだけど。.";
       
        $userText = json_encode([
            "question" => $question,
            "answer" => $userAnswer,
        ], JSON_UNESCAPED_UNICODE);


        $client = new Client();

        $res = $client->post("https://api.openai.com/v1/responses", [
            "headers" => [
                "Authorization" => "Bearer " . env("OPENAI_API_KEY"),
                "Content-Type"  => "application/json",
            ],
            "json" => [
                "model" => env("OPENAI_MODEL_4"),
                "input" => [
                    [
                        "role" => "system",
                        "content" => [[
                            "type" => "input_text",
                            "text" => $systemPrompt
                        ]],
                    ],
                    [
                        "role" => "user",
                        "content" => [[
                            "type" => "input_text",
                            "text" => $userText
                        ]],
                    ],
                ],
                "text" => [
                    "format" => ["type" => "json_object"],
                    // "verbosity" => "low",                    
                ],
                "temperature" => 0,
                "max_output_tokens" => 100,
            ],
        ]);


        $body = json_decode($res->getBody(), true);


        $status = $body['status'] ?? null;
        if ($status !== 'completed') {
            // If the response is incomplete/failed, surface a useful error to the view
            $reason = $body['incomplete_details']['reason'] ?? ($body['error']['message'] ?? 'unknown');
            dd('grade-result', [
                'ok' => false,
                'error' => "Model response not completed. Reason: {$reason}",
                'scores' => null,
                'average_score' => null,
                'is_correct' => false,
                'raw' => $body,
            ]);
        }

        // 2) Extract output_text from Responses API structure
        $outText = null;

        foreach (($body['output'] ?? []) as $item) {
            if (($item['type'] ?? '') !== 'message') {
                continue;
            }
            foreach (($item['content'] ?? []) as $c) {
                if (($c['type'] ?? '') === 'output_text') {
                    $outText = $c['text'] ?? null;
                    break 2;
                }
            }
        }

        $outText = $outText ?? ($body['output_text'] ?? null);

        if (!$outText || !is_string($outText)) {
            dd('grade-result', [
                'ok' => false,
                'error' => 'No output_text found in model response.',
                'scores' => null,
                'average_score' => null,
                'is_correct' => false,
                'raw' => $body,
            ]);
        }

        // 3) Parse the JSON returned by the model
        $grade = json_decode($outText, true);
        if (!is_array($grade)) {
            dd('grade-result', [
                'ok' => false,
                'error' => 'Invalid JSON from model.',
                'scores' => null,
                'average_score' => null,
                'is_correct' => false,
                'raw_text' => $outText,
                'raw' => $body,
            ]);
        }

        // 4) Validate required keys and clamp values to 0..10
        $weights = [
            'semantic_accuracy'        => 0.60,
            'politeness_register'      => 0.20,
            'grammatical_naturalness'  => 0.10,
            'clarity_effectiveness'    => 0.10,
        ];

        $scores = [];
        foreach ($weights as $k => $weight) {

            if (!array_key_exists($k, $grade)) {
                return view('grade-result', [
                    'ok' => false,
                    'error' => "Missing key in model JSON: {$k}",
                    'scores' => null,
                    'average_score' => null,
                    'is_correct' => false,
                    'raw_text' => $outText,
                    'raw' => $body,
                ]);
            }

            $v = (float) $grade[$k];

            // Clamp to 0..10
            if ($v < 0) $v = 0;
            if ($v > 10) $v = 10;

            $scores[$k] = round($v, 1);
        }

        // 5) Compute average (you said you will do it in code)
        $total = 0.0;
        foreach ($weights as $key => $weight) {
            $score = $scores[$key] ?? 0;   // safety fallback
            $total += $score * $weight;
        }
        return ([
            "score" => round($total, 1),
            "is_correct" => $total >= 7,
        ]);


    }

}