<?php

namespace Tests\Feature\OpenAI;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class OpenAIControllerTest extends TestCase
{
    use WithFaker;

    private User $user;

    private OrganizationUnit $orgUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
        ]);
    }

    // ─── Enhance ─────────────────────────────────────────────

    public function test_enhance_returns_400_when_reason_missing(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/enhance', []);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing reason']);
    }

    public function test_enhance_returns_400_when_reason_is_empty_string(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/enhance', [
            'reason' => '',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing reason']);
    }

    public function test_enhance_returns_500_when_ai_model_not_configured(): void
    {
        config(['openai.model' => null]);

        $response = $this->actingAs($this->user)->postJson('/ai/enhance', [
            'reason' => 'Fixing production bug',
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'AI feature not configured']);
    }

    // ─── Analyze ─────────────────────────────────────────────

    public function test_analyze_returns_400_when_content_missing(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/analyze', []);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing content']);
    }

    public function test_analyze_returns_400_when_content_is_empty_string(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/analyze', [
            'content' => '',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing content']);
    }

    public function test_analyze_returns_400_when_content_is_null(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/analyze', [
            'content' => null,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing content']);
    }

    public function test_analyze_returns_500_when_ai_model_not_configured(): void
    {
        config(['openai.model' => null]);

        $response = $this->actingAs($this->user)->postJson('/ai/analyze', [
            'content' => 'Some report data',
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'AI feature not configured']);
    }

    // ─── Summarize ──────────────────────────────────────────

    public function test_summarize_returns_400_when_employees_missing(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/summarize', []);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing employees']);
    }

    public function test_summarize_returns_400_when_employees_is_empty_array(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [],
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing employees']);
    }

    public function test_summarize_returns_400_when_employee_payload_is_invalid(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 'not-a-number', 'reasons' => ['fixing bug']],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid employees']);
    }

    public function test_summarize_returns_400_when_employee_has_no_reasons(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 2, 'reasons' => []],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid employees']);
    }

    public function test_summarize_returns_400_when_too_many_employees(): void
    {
        $employees = [];
        for ($i = 0; $i < 51; $i++) {
            $employees[] = ['name' => "Employee {$i}", 'hours' => 1, 'reasons' => ['fixing bug']];
        }

        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => $employees,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Too many employees']);
    }

    public function test_summarize_returns_500_when_ai_model_not_configured(): void
    {
        config(['openai.model' => null]);

        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 3, 'reasons' => ['fixing bug']],
            ],
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'AI feature not configured']);
    }

    public function test_summarize_returns_summaries_per_employee_in_order(): void
    {
        config(['openai.model' => 'test-model']);

        OpenAI::fake([
            $this->fakeChatResponse(json_encode([
                'summaries' => ['Jane justification', 'Mark justification'],
            ])),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 3, 'reasons' => ['asdf', 'deployment support']],
                ['name' => 'Mark Lim', 'hours' => 5, 'reasons' => ['fixing production bug']],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 3, 'justification' => 'Jane justification'],
                ['name' => 'Mark Lim', 'hours' => 5, 'justification' => 'Mark justification'],
            ],
        ]);

        OpenAI::assertSent(Chat::class);
    }

    public function test_summarize_returns_500_when_summary_count_mismatches(): void
    {
        config(['openai.model' => 'test-model']);

        OpenAI::fake([
            $this->fakeChatResponse(json_encode([
                'summaries' => ['Only one'],
            ])),
        ]);

        $response = $this->actingAs($this->user)->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 3, 'reasons' => ['fixing bug']],
                ['name' => 'Mark Lim', 'hours' => 5, 'reasons' => ['deployment support']],
            ],
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Failed to generate summary']);
    }

    // ─── Auth ───────────────────────────────────────────────

    public function test_enhance_requires_authentication(): void
    {
        $response = $this->postJson('/ai/enhance', ['reason' => 'test']);

        $response->assertUnauthorized();
    }

    public function test_analyze_requires_authentication(): void
    {
        $response = $this->postJson('/ai/analyze', ['content' => 'data']);

        $response->assertUnauthorized();
    }

    public function test_summarize_requires_authentication(): void
    {
        $response = $this->postJson('/ai/summarize', [
            'employees' => [
                ['name' => 'Jane Cruz', 'hours' => 3, 'reasons' => ['fixing bug']],
            ],
        ]);

        $response->assertUnauthorized();
    }

    private function fakeChatResponse(string $content): CreateResponse
    {
        return CreateResponse::fake([
            'choices' => [
                ['message' => ['content' => $content]],
            ],
        ]);
    }
}
