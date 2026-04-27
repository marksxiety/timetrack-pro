<?php

namespace Tests\Feature\OpenAI;

use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\WithFaker;
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
}
