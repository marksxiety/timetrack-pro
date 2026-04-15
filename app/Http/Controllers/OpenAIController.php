<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIController extends Controller
{
    public function enhance(Request $request)
    {
        $reason = $request->input('reason');
        if (!$reason) {
            return response()->json(['error' => 'Missing reason'], 400);
        }

        // --- STEP 1: INITIAL AI VALIDATION (The Guardrail) ---
        $validator = OpenAI::chat()->create([
            'model' => env('AI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Analyze the user's overtime reason. Determine if it is a valid, work-related task or intent. " .
                        "If it is gibberish (e.g., 'asdf'), purely emojis, offensive, or completely unrelated to work, return 'INVALID'. " .
                        "If it is a potential work reason, even if short, return 'VALID'. " .
                        "Return ONLY the word VALID or INVALID."
                ],
                ['role' => 'user', 'content' => $reason],
            ],
            'temperature' => 0, // Keep it precise
        ]);

        $isValid = trim($validator->choices[0]->message->content);

        if (str_contains($isValid, 'INVALID')) {
            return response()->json(['error' => 'Please provide a valid work-related reason.'], 422);
        }

        // --- STEP 2: THE ENHANCEMENT STREAM (Main Logic) ---
        $response = new StreamedResponse(function () use ($reason) {
            $stream = OpenAI::chat()->createStreamed([
                'model' => env('AI_MODEL', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an expert at refining work logs. " .
                            "Rewrite the input as a professional, objective statement using an action verb. " .
                            "Avoid personal pronouns. " .
                            "Tone: Productive and concise. " .
                            "Constraint: Response must not exceed 16,777,215 characters. " .
                            "Return only the enhanced text."
                    ],
                    ['role' => 'user', 'content' => $reason],
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
            ]);

            foreach ($stream as $event) {
                if (isset($event->choices[0]->delta->content)) {
                    echo $event->choices[0]->delta->content;
                    ob_flush();
                    flush();
                }
            }
        });

        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function analyze(Request $request)
    {
        $content = $request->input('content');
        if (!$content) {
            return response()->json(['error' => 'Missing content'], 400);
        }

        // Streamed response for incremental output
        $response = new StreamedResponse(function () use ($content) {
            $stream = OpenAI::chat()->createStreamed([
                'model' => env('AI_MODEL', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a senior workforce analytics consultant specializing in overtime analysis for management decision-making.

                        **Your Primary Objectives:**
                        1. Identify and categorize the most common reasons employees require overtime
                        2. Analyze shift patterns (day vs. night) where overtime frequently occurs
                        3. Provide strategic insights to help management understand overtime necessity
                        4. Present findings in a professional, data-driven manner

                        **Output Format:** Markdown report with executive summary, grouped reasons, shift distribution, and management insights."
                    ],
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
            ]);

            // flush data as it's received
            foreach ($stream as $event) {
                if (isset($event->choices[0]->delta->content)) {
                    echo $event->choices[0]->delta->content;
                    ob_flush();
                    flush();
                }
            }
        });

        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
