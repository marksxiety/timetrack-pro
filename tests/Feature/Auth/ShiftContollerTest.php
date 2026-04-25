<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\Shift;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShiftContollerTest extends TestCase
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

    // ─── Insert Shift Code ───────────────────────────────────

    public function test_insert_shift_code_without_time(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'REST',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shift_codes', ['code' => 'REST']);
    }

    public function test_insert_shift_code_with_time(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'DAY',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'timerequired' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shift_codes', [
            'code' => 'DAY',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);
    }

    public function test_insert_shift_code_fails_with_duplicate_code(): void
    {
        Shift::create(['code' => 'DAY']);

        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'DAY',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_insert_shift_code_fails_with_end_before_start(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'DAY',
            'start_time' => '17:00',
            'end_time' => '08:00',
            'timerequired' => true,
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    public function test_insert_shift_code_fails_without_code(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', []);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_insert_shift_code_fails_with_long_code(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'VERYLONGCODE',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_insert_shift_code_requires_time_when_timerequired(): void
    {
        $response = $this->actingAs($this->approver)->post('/shift/register', [
            'code' => 'DAY',
            'timerequired' => true,
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    // ─── Update Shift Code ───────────────────────────────────

    public function test_update_shift_code(): void
    {
        $shift = Shift::create(['code' => 'DAY']);

        $response = $this->actingAs($this->approver)->put("/shift/{$shift->id}", [
            'code' => 'MORNING',
            'timerequired' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('MORNING', $shift->fresh()->code);
    }

    public function test_update_shift_code_fails_with_duplicate_code_ignoring_self(): void
    {
        Shift::create(['code' => 'DAY']);
        $shift = Shift::create(['code' => 'NIGHT']);

        $response = $this->actingAs($this->approver)->put("/shift/{$shift->id}", [
            'code' => 'DAY',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_update_shift_code_allows_same_code(): void
    {
        $shift = Shift::create(['code' => 'DAY']);

        $response = $this->actingAs($this->approver)->put("/shift/{$shift->id}", [
            'code' => 'DAY',
            'timerequired' => true,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_update_shift_code_without_time(): void
    {
        $shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response = $this->actingAs($this->approver)->put("/shift/{$shift->id}", [
            'code' => 'DAY',
            'timerequired' => true,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_update_shift_code_fails_without_code(): void
    {
        $shift = Shift::create(['code' => 'DAY']);

        $response = $this->actingAs($this->approver)->put("/shift/{$shift->id}", []);

        $response->assertSessionHasErrors(['code']);
    }

    // ─── Registered Shift Codes ──────────────────────────────

    public function test_registered_shift_codes_returns_all(): void
    {
        Shift::create(['code' => 'DAY']);
        Shift::create(['code' => 'NIGHT']);

        $response = $this->actingAs($this->approver)->get('/shift');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Maintenance/ShiftCodes')
                ->has('shiftcodes', 2);
        });
    }

    public function test_registered_shift_codes_returns_empty(): void
    {
        $response = $this->actingAs($this->approver)->get('/shift');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Maintenance/ShiftCodes')
                ->where('shiftcodes', []);
        });
    }

    // ─── Delete Shift Code ───────────────────────────────────

    public function test_delete_shift_code(): void
    {
        $shift = Shift::create(['code' => 'DAY']);

        $response = $this->actingAs($this->approver)->delete("/shift/{$shift->id}");

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('shift_codes', ['id' => $shift->id]);
    }

    public function test_delete_shift_code_with_fk_constraint_returns_error(): void
    {
        $shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $employee = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
        ]);

        Schedule::create([
            'user_id' => $employee->id,
            'shift_id' => $shift->id,
            'date' => '2026-01-04',
            'week' => 1,
        ]);

        $response = $this->actingAs($this->approver)->delete("/shift/{$shift->id}");

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseHas('shift_codes', ['id' => $shift->id]);
    }

    // ─── Shift Code List ─────────────────────────────────────

    public function test_shift_code_list_returns_json(): void
    {
        Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response = $this->actingAs($this->approver)->getJson('/approver/shift/list');

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'success',
        ]);
        $response->assertJsonCount(1, 'data');
    }

    public function test_shift_code_list_returns_empty(): void
    {
        $response = $this->actingAs($this->approver)->getJson('/approver/shift/list');

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'success',
            'data' => [],
        ]);
    }
}
