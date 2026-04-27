<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class FetchOvertimeRequestsBySessionTest extends TestCase
{
    private User $user;
    private OrganizationUnit $orgUnit;
    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->user = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $this->actingAs($this->user);

        $this->shift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    private function createSchedule(string $date, int $week = 1): Schedule
    {
        return Schedule::create([
            'user_id' => $this->user->id,
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

    public function test_returns_inertia_employee_index()
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->component('Employee/Index'));
    }

    public function test_stats_defaults_to_zero()
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('stats')
            ->where('stats.total_overtime_hours', '0.00')
            ->where('stats.tentative_overtime_hours', '0.00')
            ->where('stats.approved_requests', 0)
            ->where('stats.pending_requests', 0)
            ->where('stats.rejected_requests', 0)
        );
    }

    public function test_stats_count_approved_requests()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'APPROVED', '3.00');
        $this->createOvertime($schedule, 'FILED', '2.00');

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('stats.approved_requests', 2)
            ->where('stats.total_overtime_hours', '5.00')
            ->where('stats.tentative_overtime_hours', '5.00')
            ->where('stats.pending_requests', 0)
        );
    }

    public function test_stats_count_pending_requests()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'PENDING', '1.00');
        $this->createOvertime($schedule, 'APPROVED', '2.00');

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('stats.pending_requests', 1)
            ->where('stats.tentative_overtime_hours', '3.00')
            ->where('stats.total_overtime_hours', '2.00')
        );
    }

    public function test_stats_count_rejected_requests()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'DISAPPROVED');
        $this->createOvertime($schedule, 'DISAPPROVED');

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('stats.rejected_requests', 2)
        );
    }

    public function test_recent_requests_list_limited_to_five()
    {
        for ($i = 0; $i < 7; $i++) {
            $schedule = $this->createSchedule('2026-01-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT));
            $this->createOvertime($schedule, 'PENDING');
        }

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('info.recentRequestsList', 5)
        );
    }

    public function test_monthly_filter_with_query_params()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'APPROVED');

        $response = $this->get('/?month=1&year=2026');

        $response->assertInertia(fn ($page) => $page
            ->has('info.overtimelist')
            ->where('payload.year', '2026')
            ->where('payload.month', '1')
        );
    }

    public function test_does_not_show_other_users_requests()
    {
        $otherUser = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $schedule = Schedule::create([
            'user_id' => $otherUser->id,
            'shift_id' => $this->shift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);
        $this->createOvertime($schedule, 'APPROVED');

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('stats.approved_requests', 0)
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
