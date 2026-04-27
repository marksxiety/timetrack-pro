<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\RequiredHours;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class FetchOvertimeRequestsViaStatusTest extends TestCase
{
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

    private function createOvertime(Schedule $schedule, string $status, string $hours = '2.00'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => $hours,
            'reason' => 'Test reason',
            'status' => $status,
        ]);
    }

    public function test_filing_route_returns_correct_page()
    {
        $response = $this->get('/overtime/filing?page=Approver/Filing&week=1&year=2026&status=FILED');

        $response->assertInertia(fn ($page) => $page->component('Approver/Filing'));
    }

    public function test_pending_route_returns_correct_page()
    {
        $response = $this->get('/overtime/pending?page=Approver/Pending&week=1&year=2026&status=PENDING');

        $response->assertInertia(fn ($page) => $page->component('Approver/Pending'));
    }

    public function test_filed_route_returns_correct_page()
    {
        $response = $this->get('/overtime/filed?page=Approver/Filed&week=1&year=2026&status=FILED');

        $response->assertInertia(fn ($page) => $page->component('Approver/Filed'));
    }

    public function test_filters_by_pending_status()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'PENDING');
        $this->createOvertime($schedule, 'APPROVED');

        $response = $this->get('/overtime/pending?page=Approver/Pending&week=1&year=2026&status=PENDING');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests', 1)
        );
    }

    public function test_filters_by_filed_status()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'FILED');
        $this->createOvertime($schedule, 'PENDING');

        $response = $this->get('/overtime/filed?page=Approver/Filed&week=1&year=2026&status=FILED');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests', 1)
        );
    }

    public function test_remaining_hours_includes_required_hours()
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 1,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->get('/overtime/filing?page=Approver/Filing&week=1&year=2026&status=FILED');

        $response->assertInertia(fn ($page) => $page
            ->where('info.hours.limit', 40)
        );
    }

    public function test_empty_requests_for_status()
    {
        $response = $this->get('/overtime/pending?page=Approver/Pending&week=1&year=2026&status=PENDING');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests', 0)
        );
    }

    public function test_filters_by_org_unit()
    {
        $otherOrg = OrganizationUnit::factory()->create(['unit_path' => 'Other Unit']);
        $otherEmployee = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $otherOrg->id,
        ]);
        $otherSchedule = Schedule::create([
            'user_id' => $otherEmployee->id,
            'shift_id' => $this->shift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);
        OvertimeRequest::create([
            'employee_schedule_id' => $otherSchedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '2.00',
            'reason' => 'Other reason',
            'status' => 'PENDING',
        ]);

        $response = $this->get('/overtime/pending?page=Approver/Pending&week=1&year=2026&status=PENDING');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests', 0)
        );
    }

    public function test_request_structure_has_expected_keys()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'PENDING');

        $response = $this->get('/overtime/pending?page=Approver/Pending&week=1&year=2026&status=PENDING');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.0.user')
            ->has('info.requests.0.schedule')
            ->has('info.requests.0.overtime')
        );
    }
}
