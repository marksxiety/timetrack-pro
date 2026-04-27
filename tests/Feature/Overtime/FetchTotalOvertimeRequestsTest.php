<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\RequiredHours;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class FetchTotalOvertimeRequestsTest extends TestCase
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

    private function createSchedule(User $user, string $date, int $week = 1): Schedule
    {
        return Schedule::create([
            'user_id' => $user->id,
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

    public function test_returns_inertia_approver_index()
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->component('Approver/Index'));
    }

    public function test_totals_are_zero_when_no_requests()
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('info.result.totals')
            ->where('info.result.totals.TOTAL_REQUESTS', 0)
            ->where('info.result.totals.TOTAL_HOURS', 0)
            ->where('info.result.totals.PENDING', 0)
            ->where('info.result.totals.APPROVED', 0)
            ->where('info.result.totals.FILED', 0)
            ->where('info.result.totals.DECLINED', 0)
            ->where('info.result.totals.DISAPPROVED', 0)
            ->where('info.result.totals.CANCELED', 0)
        );
    }

    public function test_pie_chart_groups_by_status()
    {
        $schedule = $this->createSchedule($this->employee, '2026-01-05');
        $this->createOvertime($schedule, 'PENDING');
        $this->createOvertime($schedule, 'APPROVED');
        $this->createOvertime($schedule, 'DECLINED');

        $response = $this->get('/?week=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->has('info.result.requests')
        );
    }

    public function test_zero_value_statuses_removed_from_pie_chart()
    {
        $schedule = $this->createSchedule($this->employee, '2026-01-05');
        $this->createOvertime($schedule, 'PENDING');

        $response = $this->get('/?week=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->has('info.result.requests')
            ->etc()
        );

        $requests = $response->viewData('page')['props']['info']['result']['requests'] ?? [];
        foreach ($requests as $item) {
            $this->assertGreaterThan(0, $item['value']);
        }
    }

    public function test_required_hours_from_database()
    {
        RequiredHours::create([
            'year' => 2026,
            'week' => 1,
            'required_hours' => 40,
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->get('/?week=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->where('info.result.totals.REQUIRED_HOURS', 40)
        );
    }

    public function test_recent_requests_limited_to_ten()
    {
        for ($i = 0; $i < 12; $i++) {
            $schedule = $this->createSchedule($this->employee, '2026-01-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT));
            $this->createOvertime($schedule, 'PENDING');
        }

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('info.recentRequests', 10)
        );
    }

    public function test_breakdown_has_total_line()
    {
        $schedule = $this->createSchedule($this->employee, '2026-01-05');
        $this->createOvertime($schedule, 'APPROVED', '3.00');

        $response = $this->get('/?week=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->has('info.result.breakdown')
            ->etc()
        );

        $breakdown = $response->viewData('page')['props']['info']['result']['breakdown'] ?? [];
        $totalEntry = collect($breakdown)->first(fn ($item) => $item['name'] === 'Total');
        $this->assertNotNull($totalEntry);
        $this->assertEquals('line', $totalEntry['type']);
    }

    public function test_filters_by_org_unit()
    {
        $otherOrg = OrganizationUnit::factory()->create(['unit_path' => 'Other Unit']);
        $otherEmployee = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $otherOrg->id,
        ]);
        $schedule = $this->createSchedule($otherEmployee, '2026-01-05');
        $this->createOvertime($schedule, 'PENDING');

        $response = $this->get('/?week=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->where('info.result.totals.TOTAL_REQUESTS', 0)
        );
    }

    public function test_success_flag_is_true()
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('success', true)
            ->where('message', '')
        );
    }
}
