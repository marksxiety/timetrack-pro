<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\OvertimeRequest;
use App\Models\OrganizationUnit;

class OvertimeRequestValidationTest extends TestCase
{
    private User $user;
    private Shift $dayShift;
    private Shift $nightShift;
    private Shift $noShift;
    private Schedule $daySchedule;
    private Schedule $nightSchedule;
    private Schedule $restDaySchedule;

    protected function setUp(): void
    {
        parent::setUp();

        $orgUnit = OrganizationUnit::factory()->create();

        $this->user = User::factory()->create([
            'employeeid' => 'EMP001',
            'role' => 'employee',
            'organization_unit_id' => $orgUnit->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);

        $this->dayShift = Shift::create([
            'code' => 'DAY',
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->nightShift = Shift::create([
            'code' => 'NIGHT',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
        ]);

        $this->noShift = Shift::create([
            'code' => 'REST',
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->daySchedule = Schedule::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->dayShift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);

        $this->nightSchedule = Schedule::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->nightShift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);

        $this->restDaySchedule = Schedule::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->noShift->id,
            'week' => 1,
            'date' => '2026-01-05',
        ]);
    }

    private function postInsertOvertime(array $overrides, Schedule $schedule): \Illuminate\Testing\TestResponse
    {
        $payload = array_merge([
            'employee_schedule_id' => $schedule->id,
            'date' => $schedule->date,
            'reason' => 'Working on urgent deliverable',
            'start_time' => '06:00',
            'end_time' => '08:00',
        ], $overrides);

        return $this->post(route('overtime.request'), $payload);
    }

    private function assertValidInsert(\Illuminate\Testing\TestResponse $response, int $scheduleId): void
    {
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'Overtime Request has been filed!');
        $this->assertDatabaseHas('overtime_requests', [
            'employee_schedule_id' => $scheduleId,
        ]);
    }

    private function assertInvalidInsert(\Illuminate\Testing\TestResponse $response, string $expectedErrorField = 'start_time'): void
    {
        $response->assertRedirect();
        $response->assertSessionHasErrors($expectedErrorField);
    }

    private function postUpdateOvertime(array $overrides, Schedule $schedule): \Illuminate\Testing\TestResponse
    {
        $existingRequest = OvertimeRequest::create([
            'employee_schedule_id' => $schedule->id,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'hours' => 2.00,
            'reason' => 'Initial reason',
            'status' => 'PENDING',
        ]);

        $payload = array_merge([
            'id' => $existingRequest->id,
            'employee_schedule_id' => $schedule->id,
            'date' => $schedule->date,
            'reason' => 'Updated reason for overtime',
            'start_time' => '06:00',
            'end_time' => '08:00',
            'update_status' => 'PENDING',
            'current_status' => 'PENDING',
        ], $overrides);

        return $this->post(route('overtime.update.employee'), $payload);
    }

    private function assertValidUpdate(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    private function assertInvalidUpdate(\Illuminate\Testing\TestResponse $response, string $expectedErrorField = 'start_time'): void
    {
        $response->assertRedirect();
        $response->assertSessionHasErrors($expectedErrorField);
    }

    // ========================================================================
    // DAY SHIFT — Before shift (shift: 8AM–6PM, minimum: 1hr)
    // ========================================================================

    public function test_before_shift_2hrs_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_before_shift_1hr_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_before_shift_30min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '07:30'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_before_shift_1hr_not_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '07:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_before_shift_2hrs_not_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '05:00', 'end_time' => '07:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_before_shift_30min_another_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:30', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_before_shift_45min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:15', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    // ========================================================================
    // DAY SHIFT — After shift (shift: 8AM–6PM, minimum: 1hr)
    // ========================================================================

    public function test_after_shift_2hrs_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '20:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_after_shift_1hr_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '19:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_after_shift_30min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '18:30'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_after_shift_2hrs_not_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '19:00', 'end_time' => '21:00'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    public function test_after_shift_45min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '18:45'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_after_shift_1hr_not_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:30', 'end_time' => '19:30'], $this->daySchedule);
        $this->assertValidInsert($response, $this->daySchedule->id);
    }

    // ========================================================================
    // DAY SHIFT — Inside / Overlapping shift
    // ========================================================================

    public function test_entirely_inside_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '09:00', 'end_time' => '11:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_entirely_inside_shift_midday_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '12:00', 'end_time' => '13:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_start_at_boundary_end_inside_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '08:00', 'end_time' => '09:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_start_inside_end_at_boundary_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '17:00', 'end_time' => '18:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_straddles_shift_end_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '16:00', 'end_time' => '19:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_straddles_shift_start_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '09:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_wraps_entire_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:30', 'end_time' => '18:30'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    // ========================================================================
    // DAY SHIFT — Swapped / Inverted times (End <= Start)
    // ========================================================================

    public function test_swapped_end_before_start_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '08:00', 'end_time' => '06:00'], $this->daySchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_zero_duration_same_times_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '08:00', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_zero_duration_evening_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '18:00'], $this->daySchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_swapped_evening_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '18:00', 'end_time' => '17:00'], $this->daySchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_swapped_morning_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '06:00'], $this->daySchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_swapped_9to5_shift_invalid(): void
    {
        $nineToFiveShift = Shift::create(['code' => '9TO5', 'start_time' => '09:00:00', 'end_time' => '17:00:00']);
        $schedule = Schedule::create([
            'user_id' => $this->user->id,
            'shift_id' => $nineToFiveShift->id,
            'week' => 1,
            'date' => '2026-01-06',
        ]);

        $response = $this->postInsertOvertime(['start_time' => '09:00', 'end_time' => '08:00'], $schedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    // ========================================================================
    // DAY SHIFT — Exact same time as shift
    // ========================================================================

    public function test_exact_same_time_as_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '08:00', 'end_time' => '18:00'], $this->daySchedule);
        $this->assertInvalidInsert($response);
    }

    // ========================================================================
    // NIGHT SHIFT — Before shift (shift: 10PM–6AM, minimum: 1hr)
    // ========================================================================

    public function test_night_before_shift_2hrs_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '20:00', 'end_time' => '22:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_before_shift_1hr_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '21:00', 'end_time' => '22:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_before_shift_30min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '21:30', 'end_time' => '22:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_before_shift_2hrs_not_touching_boundary_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '19:00', 'end_time' => '21:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_before_shift_30min_another_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '20:00', 'end_time' => '20:30'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    // ========================================================================
    // NIGHT SHIFT — After shift (morning of next day, auto-detected)
    // ========================================================================

    public function test_night_after_shift_2hrs_auto_next_day_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '08:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_after_shift_1hr_auto_next_day_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '07:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_after_shift_30min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '06:30'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_after_shift_2hrs_later_morning_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '09:00'], $this->nightSchedule);
        $this->assertValidInsert($response, $this->nightSchedule->id);
    }

    public function test_night_after_shift_45min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '06:45'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    // ========================================================================
    // NIGHT SHIFT — Inside / Overlapping
    // ========================================================================

    public function test_night_inside_shift_evening_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '23:00', 'end_time' => '23:59'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_inside_shift_early_morning_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '02:00', 'end_time' => '04:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_start_at_boundary_end_inside_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '22:00', 'end_time' => '23:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_inside_ends_at_boundary_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '05:00', 'end_time' => '06:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_straddles_shift_start_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '21:00', 'end_time' => '23:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_straddles_shift_end_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '04:00', 'end_time' => '08:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_night_wraps_entire_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '20:00', 'end_time' => '20:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    // ========================================================================
    // NIGHT SHIFT — Swapped / Inverted times
    // ========================================================================

    public function test_night_swapped_end_before_start_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '20:00', 'end_time' => '19:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_night_swapped_evening_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '21:00', 'end_time' => '17:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_night_swapped_at_shift_start_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '22:00', 'end_time' => '21:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_night_swapped_morning_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '06:00', 'end_time' => '05:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_night_zero_duration_morning_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '07:00', 'end_time' => '07:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    public function test_night_zero_duration_evening_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '22:00', 'end_time' => '22:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    // ========================================================================
    // NIGHT SHIFT — Exact same time as shift
    // ========================================================================

    public function test_night_exact_same_time_as_shift_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '22:00', 'end_time' => '06:00'], $this->nightSchedule);
        $this->assertInvalidInsert($response, 'end_time');
    }

    // ========================================================================
    // REST DAY — No shift (minimum: 1hr, no overlap checks)
    // ========================================================================

    public function test_rest_day_9hrs_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '08:00', 'end_time' => '17:00'], $this->restDaySchedule);
        $this->assertValidInsert($response, $this->restDaySchedule->id);
    }

    public function test_rest_day_30min_below_minimum_invalid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '09:00', 'end_time' => '09:30'], $this->restDaySchedule);
        $this->assertInvalidInsert($response);
    }

    public function test_rest_day_crosses_midnight_4hrs_valid(): void
    {
        $response = $this->postInsertOvertime(['start_time' => '22:00', 'end_time' => '02:00'], $this->restDaySchedule);
        $this->assertValidInsert($response, $this->restDaySchedule->id);
    }

    // ========================================================================
    // UPDATE (status = PENDING) — Day shift validation
    // ========================================================================

    public function test_update_before_shift_valid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '06:00', 'end_time' => '08:00'], $this->daySchedule);
        $this->assertValidUpdate($response);
    }

    public function test_update_after_shift_valid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '18:00', 'end_time' => '20:00'], $this->daySchedule);
        $this->assertValidUpdate($response);
    }

    public function test_update_inside_shift_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '09:00', 'end_time' => '11:00'], $this->daySchedule);
        $this->assertInvalidUpdate($response);
    }

    public function test_update_wraps_shift_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '07:30', 'end_time' => '18:30'], $this->daySchedule);
        $this->assertInvalidUpdate($response);
    }

    public function test_update_swapped_times_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '18:00', 'end_time' => '06:00'], $this->daySchedule);
        $this->assertInvalidUpdate($response, 'end_time');
    }

    public function test_update_zero_duration_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '18:00', 'end_time' => '18:00'], $this->daySchedule);
        $this->assertInvalidUpdate($response, 'end_time');
    }

    public function test_update_below_minimum_hours_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '18:00', 'end_time' => '18:30'], $this->daySchedule);
        $this->assertInvalidUpdate($response);
    }

    // ========================================================================
    // UPDATE (status = PENDING) — Night shift validation
    // ========================================================================

    public function test_update_night_before_shift_valid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '20:00', 'end_time' => '22:00'], $this->nightSchedule);
        $this->assertValidUpdate($response);
    }

    public function test_update_night_after_shift_auto_next_day_valid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '06:00', 'end_time' => '08:00'], $this->nightSchedule);
        $this->assertValidUpdate($response);
    }

    public function test_update_night_inside_shift_evening_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '23:00', 'end_time' => '23:30'], $this->nightSchedule);
        $this->assertInvalidUpdate($response);
    }

    public function test_update_night_inside_shift_morning_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '03:00', 'end_time' => '04:00'], $this->nightSchedule);
        $this->assertInvalidUpdate($response);
    }

    public function test_update_night_wraps_shift_invalid(): void
    {
        $response = $this->postUpdateOvertime(['start_time' => '20:00', 'end_time' => '20:00'], $this->nightSchedule);
        $this->assertInvalidUpdate($response, 'end_time');
    }

}
