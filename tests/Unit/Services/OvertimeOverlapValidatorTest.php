<?php

namespace Tests\Unit\Services;

use App\Services\OvertimeOverlapValidator;
use Carbon\Carbon;
use Tests\TestCase;

class OvertimeOverlapValidatorTest extends TestCase
{
    private OvertimeOverlapValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new OvertimeOverlapValidator();
    }

    // -- No shift times (REST day) --

    public function test_no_shift_times_returns_valid()
    {
        $start = Carbon::create(2026, 1, 5, 2, 0);
        $end = Carbon::create(2026, 1, 5, 4, 0);

        $result = $this->validator->validate($start, $end, null, null, '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-05 02:00', $result['start']->format('Y-m-d H:i'));
        $this->assertSame('2026-01-05 04:00', $result['end']->format('Y-m-d H:i'));
    }

    // -- Swapped / inverted times --

    public function test_end_before_start_on_night_shift_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 20, 0);
        $end = Carbon::create(2026, 1, 5, 19, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('end_time', $result['errors']);
    }

    public function test_end_equal_to_start_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 8, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    // -- Day shift scenarios --

    public function test_day_shift_overtime_before_shift_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    public function test_day_shift_overtime_after_shift_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 17, 0);
        $end = Carbon::create(2026, 1, 5, 20, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    public function test_day_shift_overtime_touching_start_boundary_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    public function test_day_shift_overtime_overlapping_shift_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 7, 0);
        $end = Carbon::create(2026, 1, 5, 9, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('start_time', $result['errors']);
        $this->assertArrayHasKey('end_time', $result['errors']);
    }

    public function test_day_shift_overtime_inside_shift_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 10, 0);
        $end = Carbon::create(2026, 1, 5, 12, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_day_shift_overtime_wrapping_shift_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 7, 0);
        $end = Carbon::create(2026, 1, 5, 18, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    // -- Night shift scenarios --

    public function test_night_shift_am_overtime_before_shift_is_valid()
    {
        $start = Carbon::create(2026, 1, 6, 2, 0);
        $end = Carbon::create(2026, 1, 6, 4, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    public function test_night_shift_am_overtime_adjusts_to_next_day()
    {
        $start = Carbon::create(2026, 1, 6, 2, 0);
        $end = Carbon::create(2026, 1, 6, 4, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-07', $result['start']->format('Y-m-d'));
        $this->assertSame('2026-01-07', $result['end']->format('Y-m-d'));
    }

    public function test_night_shift_pm_overtime_after_shift_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 21, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    public function test_night_shift_overtime_overlapping_shift_returns_error()
    {
        $start = Carbon::create(2026, 1, 5, 21, 0);
        $end = Carbon::create(2026, 1, 5, 23, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_night_shift_am_overtime_straddling_shift_end_considered_after_shift()
    {
        $start = Carbon::create(2026, 1, 6, 5, 0);
        $end = Carbon::create(2026, 1, 6, 7, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-07 05:00', $result['start']->format('Y-m-d H:i'));
        $this->assertSame('2026-01-07 07:00', $result['end']->format('Y-m-d H:i'));
    }

    // -- Cross-midnight day shift scenarios --

    public function test_day_shift_cross_midnight_6pm_to_4am_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 4, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-05 18:00', $result['start']->format('Y-m-d H:i'));
        $this->assertSame('2026-01-06 04:00', $result['end']->format('Y-m-d H:i'));
    }

    public function test_day_shift_cross_midnight_6pm_to_2am_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 2, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-06 02:00', $result['end']->format('Y-m-d H:i'));
    }

    public function test_day_shift_cross_midnight_8pm_to_5am_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 20, 0);
        $end = Carbon::create(2026, 1, 5, 5, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-05 20:00', $result['start']->format('Y-m-d H:i'));
        $this->assertSame('2026-01-06 05:00', $result['end']->format('Y-m-d H:i'));
    }

    public function test_day_shift_cross_midnight_6pm_to_12am_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 0, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-06 00:00', $result['end']->format('Y-m-d H:i'));
    }

    public function test_day_shift_cross_midnight_6pm_to_5pm_is_valid()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 17, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertSame('2026-01-06 17:00', $result['end']->format('Y-m-d H:i'));
    }

    public function test_day_shift_cross_midnight_rejects_when_start_inside_shift()
    {
        $start = Carbon::create(2026, 1, 5, 8, 0);
        $end = Carbon::create(2026, 1, 5, 4, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('start_time', $result['errors']);
    }

    public function test_day_shift_cross_midnight_rejects_when_start_inside_shift_7am()
    {
        $start = Carbon::create(2026, 1, 5, 7, 0);
        $end = Carbon::create(2026, 1, 5, 6, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_day_shift_cross_midnight_9am_to_8am_rejects_for_9am_shift()
    {
        $start = Carbon::create(2026, 1, 5, 9, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '09:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_day_shift_zero_duration_rejected()
    {
        $start = Carbon::create(2026, 1, 5, 8, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('end_time', $result['errors']);
    }

    public function test_day_shift_zero_duration_at_6pm_rejected()
    {
        $start = Carbon::create(2026, 1, 5, 18, 0);
        $end = Carbon::create(2026, 1, 5, 18, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '18:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_night_shift_zero_duration_rejected()
    {
        $start = Carbon::create(2026, 1, 5, 22, 0);
        $end = Carbon::create(2026, 1, 5, 22, 0);

        $result = $this->validator->validate($start, $end, '22:00:00', '06:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
    }

    public function test_no_shift_zero_duration_returns_valid()
    {
        $start = Carbon::create(2026, 1, 5, 8, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, null, null, '2026-01-05');

        $this->assertTrue($result['valid']);
    }

    // -- Return value integrity --

    public function test_valid_result_contains_start_and_end_carbon_instances()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertTrue($result['valid']);
        $this->assertInstanceOf(Carbon::class, $result['start']);
        $this->assertInstanceOf(Carbon::class, $result['end']);
    }

    public function test_invalid_result_contains_errors_array()
    {
        $start = Carbon::create(2026, 1, 5, 9, 0);
        $end = Carbon::create(2026, 1, 5, 10, 0);

        $result = $this->validator->validate($start, $end, '08:00:00', '17:00:00', '2026-01-05');

        $this->assertFalse($result['valid']);
        $this->assertIsArray($result['errors']);
        $this->assertNotEmpty($result['errors']);
    }
}
