<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIController extends Controller
{
    public function enhance(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an expert at refining overtime request reasons. Take the user's short reason and expand it into 2–3 natural, professional sentences that explain WHY they need overtime. Keep it practical and work-focused. Do not make it sound too formal. Return only the enhanced text without quotes or explanations. Response must not exceed 16,777,215 characters."
                    ],
                    [
                        'role' => 'user',
                        'content' => $request->reason,
                    ],
                ],
                'temperature' => 0.5,
                'max_tokens' => 300,
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'enhanced_reason',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'enhanced_text' => [
                                    'type' => 'string',
                                    'maxLength' => 16777215,
                                    'description' => 'The enhanced overtime reason text',
                                ],
                            ],
                            'required' => ['enhanced_text'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

            $data = json_decode($response['choices'][0]['message']['content'], true);

            return response()->json([
                'success' => true,
                'enhanced_text' => $data['enhanced_text'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function analyze(Request $request)
    {
        $content = $request->input('reason');
        if (!$content) {
            return response()->json(['error' => 'Missing content'], 400);
        }

        // Streamed response for incremental output
        $response = new StreamedResponse(function () use ($content) {
            $stream = OpenAI::chat()->createStreamed([
                'model' => 'gpt-4o-mini',
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
