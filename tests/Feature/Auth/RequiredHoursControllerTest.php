<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\RequiredHours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequiredHoursControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $approver;
    private OrganizationUnit $orgUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);
    }

    // ─── Register Required Hours ─────────────────────────────

    public function test_register_required_hours_successfully(): void
    {
        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('required_hours', [
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);
    }

    public function test_register_required_hours_fails_with_duplicate_year_week(): void
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'week' => 15,
            'required_hours' => 45,
        ]);

        $response->assertSessionHasErrors(['year', 'week']);
    }

    public function test_register_required_hours_fails_without_year(): void
    {
        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'week' => 15,
            'required_hours' => 40,
        ]);

        $response->assertSessionHasErrors(['year']);
    }

    public function test_register_required_hours_fails_without_week(): void
    {
        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'required_hours' => 40,
        ]);

        $response->assertSessionHasErrors(['week']);
    }

    public function test_register_required_hours_fails_without_required_hours(): void
    {
        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'week' => 15,
        ]);

        $response->assertSessionHasErrors(['required_hours']);
    }

    public function test_register_required_hours_allows_different_year_week_same_org(): void
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'week' => 16,
            'required_hours' => 40,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('required_hours', ['year' => 2026, 'week' => 16]);
    }

    public function test_register_required_hours_does_not_conflict_across_org_units(): void
    {
        $otherOrgUnit = OrganizationUnit::factory()->create();
        RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $otherOrgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->post('/hours/register', [
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
        ]);

        $response->assertSessionHasNoErrors();
    }

    // ─── Registered Required Hours ───────────────────────────

    public function test_registered_required_hours_returns_records_for_org_unit(): void
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        RequiredHours::create([
            'year' => 2026,
            'week' => 16,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->get('/hours');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Maintenance/RequiredHours')
                ->has('requiredhours', 2);
        });
    }

    public function test_registered_required_hours_returns_empty_for_no_records(): void
    {
        $response = $this->actingAs($this->approver)->get('/hours');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Maintenance/RequiredHours')
                ->where('requiredhours', []);
        });
    }

    // ─── Update Required Hour ────────────────────────────────

    public function test_update_required_hour_successfully(): void
    {
        $hours = RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->put("/hours/{$hours->id}", [
            'year' => 2026,
            'week' => 15,
            'required_hours' => 48,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(48, $hours->fresh()->required_hours);
    }

    public function test_update_required_hour_fails_without_year(): void
    {
        $hours = RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->put("/hours/{$hours->id}", [
            'week' => 15,
            'required_hours' => 48,
        ]);

        $this->assertEquals(40, $hours->fresh()->required_hours);
    }

    public function test_update_required_hour_fails_without_week(): void
    {
        $hours = RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->put("/hours/{$hours->id}", [
            'year' => 2026,
            'required_hours' => 48,
        ]);

        $this->assertEquals(40, $hours->fresh()->required_hours);
    }

    public function test_update_required_hour_fails_without_required_hours(): void
    {
        $hours = RequiredHours::create([
            'year' => 2026,
            'week' => 15,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($this->approver)->put("/hours/{$hours->id}", [
            'year' => 2026,
            'week' => 15,
        ]);

        $this->assertEquals(40, $hours->fresh()->required_hours);
    }
}
