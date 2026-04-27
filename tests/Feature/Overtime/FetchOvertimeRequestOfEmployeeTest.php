<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class FetchOvertimeRequestOfEmployeeTest extends TestCase
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

    private function createOvertime(Schedule $schedule, string $status = 'PENDING'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '2.00',
            'reason' => 'Test reason for search',
            'status' => $status,
        ]);
    }

    public function test_returns_inertia_employee_request()
    {
        $response = $this->get('/overtime/requests');

        $response->assertInertia(fn ($page) => $page->component('Employee/Request'));
    }

    public function test_pagination_limits_to_ten()
    {
        for ($i = 0; $i < 12; $i++) {
            $schedule = $this->createSchedule('2026-01-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT));
            $this->createOvertime($schedule);
        }

        $response = $this->get('/overtime/requests');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 10)
        );
    }

    public function test_filter_by_week()
    {
        $schedule = $this->createSchedule('2026-01-05', week: 1);
        $this->createOvertime($schedule);
        $otherSchedule = $this->createSchedule('2026-01-15', week: 3);
        $this->createOvertime($otherSchedule);

        $response = $this->get('/overtime/requests?week=1');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 1)
        );
    }

    public function test_filter_by_status()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'APPROVED');
        $this->createOvertime($schedule, 'PENDING');

        $response = $this->get('/overtime/requests?status=APPROVED');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 1)
        );
    }

    public function test_status_all_returns_all()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule, 'APPROVED');
        $this->createOvertime($schedule, 'PENDING');
        $this->createOvertime($schedule, 'DECLINED');

        $response = $this->get('/overtime/requests?status=ALL');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 3)
        );
    }

    public function test_search_by_reason()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule);
        $otherSchedule = $this->createSchedule('2026-01-06');
        OvertimeRequest::create([
            'employee_schedule_id' => $otherSchedule->id,
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'hours' => '2.00',
            'reason' => 'Different reason',
            'status' => 'PENDING',
        ]);

        $response = $this->get('/overtime/requests?search=search');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 1)
        );
    }

    public function test_sort_date_desc()
    {
        $schedule1 = $this->createSchedule('2026-01-15');
        $this->createOvertime($schedule1);
        $schedule2 = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule2);

        $response = $this->get('/overtime/requests?sort=date_desc');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 2)
        );
    }

    public function test_sort_date_asc()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule);

        $response = $this->get('/overtime/requests?sort=date_asc');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 1)
        );
    }

    public function test_request_has_expected_fields()
    {
        $schedule = $this->createSchedule('2026-01-05');
        $this->createOvertime($schedule);

        $response = $this->get('/overtime/requests');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data.0.id')
            ->has('info.requests.data.0.shift')
            ->has('info.requests.data.0.start_time')
            ->has('info.requests.data.0.end_time')
            ->has('info.requests.data.0.date')
            ->has('info.requests.data.0.week')
            ->has('info.requests.data.0.status')
            ->has('info.requests.data.0.hours')
            ->has('info.requests.data.0.reason')
        );
    }

    public function test_empty_state()
    {
        $response = $this->get('/overtime/requests');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 0)
            ->where('success', true)
        );
    }

    public function test_does_not_show_other_users_requests()
    {
        $otherUser = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $otherSchedule = Schedule::create([
            'user_id' => $otherUser->id,
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

        $response = $this->get('/overtime/requests');

        $response->assertInertia(fn ($page) => $page
            ->has('info.requests.data', 0)
        );
    }
}
