<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIController extends Controller
{
    private const MAX_SUMMARY_EMPLOYEES = 50;

    private const ENHANCE_VALIDATION_PROMPT = <<<'PROMPT'
    Analyze the user's overtime reason. Determine if it is a valid, work-related task or intent. If it is gibberish (e.g., 'asdf'), purely emojis, offensive, or completely unrelated to work, return 'INVALID'. If it is a potential work reason, even if short, return 'VALID'. Return ONLY the word VALID or INVALID.
    PROMPT;

    private const ENHANCE_REWRITE_PROMPT = <<<'PROMPT'
    You are an expert at refining work logs. Rewrite the input as a professional, objective statement using an action verb. Avoid personal pronouns. Tone: Productive and concise. Constraint: Response must not exceed 16,777,215 characters. Return only the enhanced text.
    PROMPT;

    private const ANALYZE_PROMPT = <<<'PROMPT'
    You are a Senior Workforce Analytics Consultant. Your task is to analyze overtime data to provide actionable management insights.

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
    - Avoid flowery language; focus on efficiency and resource allocation.
    PROMPT;

    private const SUMMARIZE_PROMPT = <<<'PROMPT'
    You are an AI generating concise overtime justifications for a Request of Authorization (ROA) workflow. All requested overtime is in the FUTURE.

    You will receive numbered employee blocks. Each block contains multiple daily overtime reasons submitted by a single employee over different days.

    For each employee block, synthesize their reasons into a single justification following these rules:
    1. Tone & Voice: Write in an objective, action-oriented imperative mood (similar to git commit messages). Start directly with action verbs (e.g., "Process", "Complete", "Resolve", "Address"). 
    2. Forbidden Phrases: Do NOT use introductory filler like "Approval is requested to...", "The employee plans to...", or "This overtime is for...". Just state the work to be done.
    3. Synthesis & Length: Write 1-2 concise sentences. Combine the core themes of the multi-day tasks. Do not merely list the days or stitch reasons together with "and".
    4. Accuracy: Cover the overall intent of the provided reasons. Do NOT invent dates, hours, tasks, or metrics. Do not mention the employee's name.
    5. Fallback: If a block has no usable or intelligible reason, use exactly: "Address pending tasks and resolve workflow backlogs."

    OUTPUT FORMAT:
    Return ONLY valid JSON in this exact shape. Do not include markdown blocks (like ```json), commentary, or extra text:
    {"summaries": ["<justification_1>", "<justification_2>"]}

    The "summaries" array must contain exactly one justification per employee block, strictly maintaining the original order.
    PROMPT;

    public function enhance(Request $request)
    {
        $reason = $request->input('reason');
        if (! $reason) {
            return response()->json(['error' => 'Missing reason'], 400);
        }

        $model = config('openai.model');
        if (! $model) {
            return response()->json(['error' => 'AI feature not configured'], 500);
        }

        $validator = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::ENHANCE_VALIDATION_PROMPT,
                ],
                ['role' => 'user', 'content' => $reason],
            ],
            'temperature' => 0,
        ]);

        $isValid = trim($validator->choices[0]->message->content);

        if (str_contains($isValid, 'INVALID')) {
            return response()->json(['error' => 'Please provide a valid work-related reason.'], 422);
        }

        return $this->streamResponse(function () use ($reason, $model) {
            return OpenAI::chat()->createStreamed([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => self::ENHANCE_REWRITE_PROMPT,
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
        if (! $content) {
            return response()->json(['error' => 'Missing content'], 400);
        }

        $model = config('openai.model');
        if (! $model) {
            return response()->json(['error' => 'AI feature not configured'], 500);
        }

        return $this->streamResponse(function () use ($content, $model) {
            return OpenAI::chat()->createStreamed([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => self::ANALYZE_PROMPT,
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
     * Summarize each employee's overtime reasons into a single approval justification.
     */
    public function summarize(Request $request)
    {
        $employees = $request->input('employees');

        if (! is_array($employees) || $employees === []) {
            return response()->json(['error' => 'Missing employees'], 400);
        }

        if (count($employees) > self::MAX_SUMMARY_EMPLOYEES) {
            return response()->json(['error' => 'Too many employees'], 400);
        }

        foreach ($employees as $employee) {
            $isValid = is_array($employee)
                && isset($employee['name'], $employee['hours'], $employee['reasons'])
                && is_string($employee['name'])
                && is_numeric($employee['hours'])
                && is_array($employee['reasons'])
                && $employee['reasons'] !== [];

            if (! $isValid) {
                return response()->json(['error' => 'Invalid employees'], 400);
            }

            foreach ($employee['reasons'] as $reason) {
                if (! is_string($reason)) {
                    return response()->json(['error' => 'Invalid employees'], 400);
                }
            }
        }

        $model = config('openai.model');
        if (! $model) {
            return response()->json(['error' => 'AI feature not configured'], 500);
        }

        $employees = array_values($employees);

        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => self::SUMMARIZE_PROMPT],
                    ['role' => 'user', 'content' => $this->buildEmployeeBlocks($employees)],
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000,
                'response_format' => ['type' => 'json_object'],
            ]);
        } catch (\Throwable) {
            return response()->json(['error' => 'Failed to generate summary'], 500);
        }

        $decoded = json_decode($response->choices[0]->message->content ?? '', true);
        $summaries = is_array($decoded) ? ($decoded['summaries'] ?? null) : null;

        if (! is_array($summaries) || count($summaries) !== count($employees)) {
            return response()->json(['error' => 'Failed to generate summary'], 500);
        }

        $rows = [];
        foreach ($employees as $index => $employee) {
            $summary = $summaries[$index] ?? null;
            $rows[] = [
                'name' => $employee['name'],
                'hours' => $employee['hours'],
                'justification' => is_string($summary) ? trim($summary) : '',
            ];
        }

        return response()->json(['success' => true, 'employees' => $rows]);
    }

    /**
     * Formats employees as numbered reason blocks for the summary prompt.
     *
     * @param  array<int, array{name: string, hours: numeric, reasons: array<int, string>}>  $employees
     */
    private function buildEmployeeBlocks(array $employees): string
    {
        $blocks = [];

        foreach ($employees as $index => $employee) {
            $lines = ['Employee '.($index + 1)];

            foreach (array_values($employee['reasons']) as $position => $reason) {
                $reason = trim($reason);
                $lines[] = ($position + 1).'. '.($reason === '' ? '(no reason provided)' : $reason);
            }

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Returns a clean streamed response from an OpenAI stream callback.
     *
     * @param  callable  $streamCallback  Returns an OpenAI streamed response
     * @param  int  $timeLimit  Max execution time in seconds
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
                    echo 'data: '.json_encode(['content' => $chunk])."\n\n";
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
