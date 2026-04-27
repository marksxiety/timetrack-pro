<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\RequiredHours;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FetchOvertimeRequestsViaDateRangeTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;
    private User $employee;
    private OrganizationUnit $orgUnit;
    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->approver = User::factory()->create([
            'role' => 'approver',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->employee = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->actingAs($this->approver);

        $this->shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    private function createSchedule(string $date, int $week = 1): Schedule
    {
        return Schedule::create([
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'week' => $week,
            'date' => $date,
        ]);
    }

    private function createOvertime(Schedule $schedule, string $status = 'APPROVED'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '2.00',
            'reason' => 'Test reason',
            'status' => $status,
        ]);
    }

    public function test_returns_inertia_approver_report()
    {
        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-31');

        $response->assertInertia(fn ($page) => $page->component('Approver/Report'));
    }

    public function test_validation_requires_dates()
    {
        $response = $this->get('/generate/report');

        $response->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_validation_end_date_after_start_date()
    {
        $response = $this->get('/generate/report?start_date=2026-01-31&end_date=2026-01-01');

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_returns_requests_in_date_range()
    {
        $schedule = $this->createSchedule('2026-01-15');
        $this->createOvertime($schedule);

        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-31&unit=' . $this->orgUnit->id);

        $response->assertInertia(fn ($page) => $page
            ->has('requests.list', 1)
        );
    }

    public function test_excludes_requests_outside_date_range()
    {
        $schedule = $this->createSchedule('2026-02-15');
        $this->createOvertime($schedule);

        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-31&unit=' . $this->orgUnit->id);

        $response->assertInertia(fn ($page) => $page
            ->has('requests.list', 0)
        );
    }

    public function test_generates_week_list()
    {
        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-14&unit=' . $this->orgUnit->id);

        $response->assertInertia(fn ($page) => $page
            ->has('weeks')
        );
    }

    public function test_maps_required_hours_to_weeks()
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 1,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-14&unit=' . $this->orgUnit->id);

        $response->assertInertia(fn ($page) => $page
            ->has('requests.required_hours')
        );
    }

    public function test_empty_state()
    {
        $response = $this->get('/generate/report?start_date=2026-01-01&end_date=2026-01-31&unit=' . $this->orgUnit->id);

        $response->assertInertia(fn ($page) => $page
            ->has('requests.list', 0)
            ->has('weeks')
        );
    }
}
