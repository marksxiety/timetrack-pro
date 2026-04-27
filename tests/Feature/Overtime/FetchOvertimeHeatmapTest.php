<?php

namespace Tests\Feature\Overtime;

use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class FetchOvertimeHeatmapTest extends TestCase
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

    private function createOvertime(Schedule $schedule, string $status = 'APPROVED', string $hours = '2.00'): OvertimeRequest
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

    public function test_returns_json_response()
    {
        $response = $this->getJson('/overtime/heatmap');

        $response->assertJsonStructure([
            'years',
            'data',
            'stats' => [
                'total_hours',
                'filed',
                'pending',
                'approved',
                'rejected',
            ],
        ]);
    }

    public function test_empty_state_returns_default_structure()
    {
        $response = $this->getJson('/overtime/heatmap');

        $response->assertJson([
            'stats' => [
                'total_hours' => '0.00',
                'filed' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ],
        ]);
    }

    public function test_empty_state_data_is_empty()
    {
        $response = $this->getJson('/overtime/heatmap');

        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_custom_date_range()
    {
        $schedule = $this->createSchedule('2026-01-10');
        $this->createOvertime($schedule, 'APPROVED');

        $response = $this->getJson('/overtime/heatmap?start_date=2026-01-01&end_date=2026-01-31');

        $response->assertSuccessful();
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_stats_aggregation()
    {
        $schedule = $this->createSchedule('2026-01-10');
        $this->createOvertime($schedule, 'APPROVED', '3.00');
        $this->createOvertime($schedule, 'FILED', '2.00');
        $this->createOvertime($schedule, 'PENDING', '1.00');
        $this->createOvertime($schedule, 'DECLINED', '1.00');

        $response = $this->getJson('/overtime/heatmap?start_date=2026-01-01&end_date=2026-01-31&statuses[]=APPROVED&statuses[]=FILED&statuses[]=PENDING&statuses[]=DECLINED&statuses[]=CANCELED&statuses[]=DISAPPROVED');

        $response->assertJsonPath('stats.approved', 1);
        $response->assertJsonPath('stats.filed', 1);
        $response->assertJsonPath('stats.pending', 1);
        $response->assertJsonPath('stats.rejected', 1);
    }

    public function test_status_filtering()
    {
        $schedule = $this->createSchedule('2026-01-10');
        $this->createOvertime($schedule, 'APPROVED', '3.00');
        $this->createOvertime($schedule, 'FILED', '2.00');

        $response = $this->getJson('/overtime/heatmap?start_date=2026-01-01&end_date=2026-01-31&statuses[]=APPROVED');

        $response->assertJsonPath('stats.approved', 1);
        $response->assertJsonPath('stats.filed', 0);
        $response->assertJsonPath('stats.total_hours', '3.00');
    }

    public function test_does_not_show_other_users_data()
    {
        $otherUser = User::factory()->create([
            'role' => 'employee',
            'organization_unit_id' => $this->orgUnit->id,
        ]);
        $otherSchedule = Schedule::create([
            'user_id' => $otherUser->id,
            'shift_id' => $this->shift->id,
            'week' => 2,
            'date' => '2026-01-10',
        ]);
        OvertimeRequest::create([
            'employee_schedule_id' => $otherSchedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => '5.00',
            'reason' => 'Other user reason',
            'status' => 'APPROVED',
        ]);

        $response = $this->getJson('/overtime/heatmap?start_date=2026-01-01&end_date=2026-01-31');

        $response->assertJsonPath('stats.approved', 0);
        $response->assertJsonPath('stats.total_hours', '0.00');
    }
}
