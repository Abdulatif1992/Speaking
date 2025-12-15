<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OpenAIController extends Controller
{
    public function score(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $systemPrompt = <<<SYS
            You are a strict language teacher. 
            Evaluate the student's answer to the given question.
            Return ONLY valid JSON with keys:
            score (0-10),
            is_correct (true/false),
            feedback (string, short, in english),
            expected_answer (string, if relevant).
            SYS;

        $userPrompt = "question: {$request->question}\nanswer: {$request->answer}";
        $client = new Client();

        $res = $client->post("https://api.openai.com/v1/responses", [
            "headers" => [
                "Authorization" => "Bearer " . env("OPENAI_API_KEY"),
                "Content-Type"  => "application/json",
            ],
            "json" => [
                "model" => env("OPENAI_MODEL", "gpt-5-mini"),
                "input" => "
                    You are a strict language teacher.
                    Evaluate the student's answer to the question.

                    question: {$request->question}
                    answer: {$request->answer}

                    Return ONLY valid JSON with:
                    score, is_correct, feedback, expected_answer.
                ",
                "response_format" => [ "type" => "json_object" ],
                "max_output_tokens" => 100,
            ],
        ]);

        $body = json_decode($res->getBody(), true);
        $grade = $body["output_text"] ?? "{}";
        $grade = json_decode($grade, true);
        dd($body, $grade);
   
        return response()->json([
            "ok" => true,
            "grade" => $grade,
        ]);
    }
}
