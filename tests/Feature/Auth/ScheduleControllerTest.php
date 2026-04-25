<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $employee;
    private OrganizationUnit $orgUnit;
    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->employee = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
        ]);
        $this->shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    // ─── Schedule Page ───────────────────────────────────────

    public function test_schedule_page_returns_shifts(): void
    {
        $response = $this->actingAs($this->employee)->get('/schedule');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Employee/Schedule')
                ->has('shifts');
        });
    }

    public function test_schedule_page_returns_empty_shifts(): void
    {
        Shift::query()->delete();

        $response = $this->actingAs($this->employee)->get('/schedule');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Employee/Schedule')
                ->where('shifts', []);
        });
    }

    // ─── Fetch Schedule ──────────────────────────────────────

    public function test_fetch_schedule_returns_7_day_template(): void
    {
        $response = $this->actingAs($this->employee)->get('/schedule/list', [
            'year' => 2026,
            'week' => 1,
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
        ]);
        $data = $response->json('schedules');
        $this->assertCount(7, $data);
    }

    public function test_fetch_schedule_merges_existing_schedules(): void
    {
        $sunday = '2026-01-04';
        Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => $sunday,
            'week' => 2,
        ]);

        $response = $this->actingAs($this->employee)->get('/schedule/list', [
            'year' => 2026,
            'week' => 2,
        ]);

        $response->assertSuccessful();
        $schedules = $response->json('schedules');
        $this->assertCount(7, $schedules);
        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => $sunday,
            'week' => 2,
        ]);
    }

    public function test_fetch_schedule_uses_defaults_without_params(): void
    {
        $response = $this->actingAs($this->employee)->get('/schedule/list');

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertCount(7, $response->json('schedules'));
    }

    public function test_fetch_schedule_returns_user_id(): void
    {
        $response = $this->actingAs($this->employee)->get('/schedule/list', [
            'year' => 2026,
            'week' => 1,
        ]);

        $response->assertJson(['id' => $this->employee->id]);
    }

    // ─── Submit Schedule ─────────────────────────────────────

    public function test_submit_schedule_creates_new_entry(): void
    {
        $response = $this->actingAs($this->employee)->postJson('/schedule/submit', [
            'schedule' => [
                [
                    'id' => null,
                    'date' => '2026-01-04',
                    'week' => 1,
                    'day' => 'Sunday',
                    'shift_code' => $this->shift->id,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
        ]);
    }

    public function test_submit_schedule_updates_existing_entry(): void
    {
        $schedule = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
            'week' => 1,
        ]);

        $newShift = Shift::create([
            'code' => 'NIGHT',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
        ]);

        $response = $this->actingAs($this->employee)->postJson('/schedule/submit', [
            'schedule' => [
                [
                    'id' => $schedule->id,
                    'date' => '2026-01-04',
                    'week' => 1,
                    'day' => 'Sunday',
                    'shift_code' => $newShift->id,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $this->assertEquals($newShift->id, $schedule->fresh()->shift_id);
    }

    public function test_submit_schedule_skips_clearing_existing_shift(): void
    {
        $schedule = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
            'week' => 1,
        ]);

        $response = $this->actingAs($this->employee)->postJson('/schedule/submit', [
            'schedule' => [
                [
                    'id' => $schedule->id,
                    'date' => '2026-01-04',
                    'week' => 1,
                    'day' => 'Sunday',
                    'shift_code' => null,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('skipped_ids', [$schedule->id]);
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
    }

    public function test_submit_schedule_skips_empty_shift_on_create(): void
    {
        $response = $this->actingAs($this->employee)->postJson('/schedule/submit', [
            'schedule' => [
                [
                    'id' => null,
                    'date' => '2026-01-04',
                    'week' => 1,
                    'day' => 'Sunday',
                    'shift_code' => null,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('schedules', [
            'user_id' => $this->employee->id,
            'date' => '2026-01-04',
        ]);
    }

    public function test_submit_schedule_mixed_create_and_update(): void
    {
        $existing = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
            'week' => 1,
        ]);

        $response = $this->actingAs($this->employee)->postJson('/schedule/submit', [
            'schedule' => [
                [
                    'id' => $existing->id,
                    'date' => '2026-01-04',
                    'week' => 1,
                    'day' => 'Sunday',
                    'shift_code' => $this->shift->id,
                ],
                [
                    'id' => null,
                    'date' => '2026-01-05',
                    'week' => 1,
                    'day' => 'Monday',
                    'shift_code' => $this->shift->id,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $schedules = $response->json('schedules');
        $this->assertCount(2, $schedules);
    }

    // ─── Get User Schedule ───────────────────────────────────

    public function test_get_user_schedule_returns_schedule_with_shift(): void
    {
        Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-05',
            'week' => 2,
        ]);

        $response = $this->actingAs($this->employee)->get('/schedule/user', [
            'year' => 2026,
            'month' => 1,
            'day' => 5,
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-05',
        ]);
    }

    public function test_get_user_schedule_returns_empty_when_not_found(): void
    {
        $response = $this->actingAs($this->employee)->get('/schedule/user', [
            'year' => 2026,
            'month' => 1,
            'day' => 5,
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'schedule' => [],
        ]);
    }

    public function test_get_user_schedule_with_null_shift_times(): void
    {
        $noTimeShift = Shift::create(['code' => 'REST']);
        Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $noTimeShift->id,
            'date' => '2026-01-05',
            'week' => 2,
        ]);

        $response = $this->actingAs($this->employee)->get('/schedule/user', [
            'year' => 2026,
            'month' => 1,
            'day' => 5,
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $noTimeShift->id,
            'date' => '2026-01-05',
        ]);
    }

    // ─── Fetch Employee Schedule (Approver) ──────────────────

    public function test_fetch_employee_schedule_returns_employee_matrix(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->get('/schedule/employee/list', [
            'year' => 2026,
            'week' => 1,
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $info = $response->json('info');
        $this->assertArrayHasKey('schedules', $info);
        $this->assertArrayHasKey('week_start', $info);
        $this->assertArrayHasKey('week_end', $info);
    }

    public function test_fetch_employee_schedule_includes_7_days_per_employee(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->get('/schedule/employee/list', [
            'year' => 2026,
            'week' => 1,
        ]);

        $employeeSchedules = $response->json('info.schedules');
        foreach ($employeeSchedules as $empSchedule) {
            $this->assertCount(7, $empSchedule['schedule']);
        }
    }

    public function test_fetch_employee_schedule_merges_db_schedules(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
            'week' => 2,
        ]);

        $response = $this->actingAs($approver)->get('/schedule/employee/list', [
            'year' => 2026,
            'week' => 2,
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $employeeSchedules = $response->json('info.schedules');
        $this->assertNotEmpty($employeeSchedules);
        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
        ]);
    }

    // ─── Submit Employee Schedules (Approver) ────────────────

    public function test_submit_employee_schedules_creates_schedule(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->postJson('/schedule/employee/submit', [
            'schedule' => [
                [
                    'user_id' => $this->employee->id,
                    'week_schedule' => [
                        [
                            'user_id' => $this->employee->id,
                            'schedule' => [
                                [
                                    'shift_id' => null,
                                    'schedule_id' => null,
                                    'date' => '2026-01-04',
                                    'week' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
    }

    public function test_submit_employee_schedules_with_empty_request(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->postJson('/schedule/employee/submit', []);

        $response->assertSuccessful();
        $response->assertJson(['success' => false]);
    }

    public function test_submit_employee_schedules_batch_create_and_update(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $existing = Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-01-04',
            'week' => 1,
        ]);

        $response = $this->actingAs($approver)->postJson('/schedule/employee/submit', [
            'schedule' => [
                [
                    'user_id' => $this->employee->id,
                    'week_schedule' => [
                        [
                            'user_id' => $this->employee->id,
                            'schedule' => [
                                [
                                    'shift_id' => $this->shift->id,
                                    'schedule_id' => $existing->id,
                                    'date' => '2026-01-04',
                                    'week' => 1,
                                ],
                                [
                                    'shift_id' => $this->shift->id,
                                    'schedule_id' => null,
                                    'date' => '2026-01-05',
                                    'week' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('schedules', ['date' => '2026-01-05', 'user_id' => $this->employee->id]);
    }

    public function test_submit_employee_schedules_skips_null_shifts(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->postJson('/schedule/employee/submit', [
            'schedule' => [
                [
                    'user_id' => $this->employee->id,
                    'week_schedule' => [
                        [
                            'user_id' => $this->employee->id,
                            'schedule' => [
                                [
                                    'shift_id' => null,
                                    'schedule_id' => null,
                                    'date' => '2026-01-04',
                                    'week' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('schedules', ['date' => '2026-01-04']);
    }
}
