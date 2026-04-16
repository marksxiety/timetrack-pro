<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIController extends Controller
{
    public function enhance(Request $request)
    {
        $reason = $request->input('reason');
        if (!$reason) {
            return response()->json(['error' => 'Missing reason'], 400);
        }

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
            'temperature' => 0,
        ]);

        $isValid = trim($validator->choices[0]->message->content);

        if (str_contains($isValid, 'INVALID')) {
            return response()->json(['error' => 'Please provide a valid work-related reason.'], 422);
        }

        return $this->streamResponse(function () use ($reason) {
            return OpenAI::chat()->createStreamed([
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
        });
    }

    public function analyze(Request $request)
    {
        $content = $request->input('content');
        if (!$content) {
            return response()->json(['error' => 'Missing content'], 400);
        }

        return $this->streamResponse(function () use ($content) {
            return OpenAI::chat()->createStreamed([
                'model' => env('AI_MODEL', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a Senior Workforce Analytics Consultant. Your task is to analyze overtime data to provide actionable management insights.

                        **Analysis Framework:**
                        1. **Root Cause Analysis (The WHY):** Categorize entries into themes like 'Technical Debt/Bug Fixing', 'Production Backlog', 'Unexpected System Downtime', or 'New Feature Implementation'.
                        2. **Operational Domain (The WHAT):** Identify which functional areas are being hit hardest (e.g., Frontend, Backend, Database, DevOps, or specific business modules).
                        3. **Shift Dynamics:** Analyze if specific issues are isolated to Day or Night shifts.

                        **Reporting Requirements:**
                        - **Executive Summary:** High-level 'health check' of current overtime trends.
                        - **Categorized Breakdown:** A table or list grouping the 'Enhanced Reasons' you see in the data.
                        - **Strategic Recommendations:** Suggest *how* to reduce this overtime (e.g., 'Night shift requires more Senior support for DevOps tasks').

                        **Formatting:**
                        - Use Markdown.
                        - Use bolding for key metrics.
                        - Maintain a cold, professional, data-driven tone.
                        - Avoid flowery language; focus on efficiency and resource allocation."
                    ],
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
            ]);
        }, 120);
    }

    /**
     * Returns a clean streamed response from an OpenAI stream callback.
     *
     * @param callable $streamCallback  Returns an OpenAI streamed response
     * @param int      $timeLimit       Max execution time in seconds
     */
    private function streamResponse(callable $streamCallback, int $timeLimit = 60): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Close session before streaming to prevent session lock blocking the response
        session_write_close();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($streamCallback, $timeLimit) {
            set_time_limit($timeLimit);

            while (ob_get_level()) {
                ob_end_clean();
            }

            $stream = $streamCallback();

            foreach ($stream as $event) {
                $chunk = $event->choices[0]->delta->content ?? null;
                if ($chunk !== null) {
                    // SSE format — works reliably across browsers and fetch() readers
                    echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }
            }

            // Signal stream end
            echo "data: [DONE]\n\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store');
        $response->headers->set('X-Accel-Buffering', 'no');   // Critical for Nginx
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}