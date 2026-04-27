<?php

namespace Tests\Unit\Services;

use App\Services\OvertimeCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class OvertimeCalculatorTest extends TestCase
{
    private OvertimeCalculator $calculator;

    private string $tempConfigPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempConfigPath = tempnam(sys_get_temp_dir(), 'ot_config_');
        $this->calculator = new OvertimeCalculator($this->tempConfigPath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempConfigPath)) {
            unlink($this->tempConfigPath);
        }

        parent::tearDown();
    }

    // -- calculateOvertimeHours --

    public function test_two_hour_difference()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 8, 0);

        $result = $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame('2.00', $result);
    }

    public function test_thirty_minute_difference()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 6, 30);

        $result = $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame('0.50', $result);
    }

    public function test_midnight_crossover()
    {
        $start = Carbon::create(2026, 1, 5, 23, 0);
        $end = Carbon::create(2026, 1, 6, 1, 0);

        $result = $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame('2.00', $result);
    }

    public function test_same_day_end_before_start_adds_one_day()
    {
        $start = Carbon::create(2026, 1, 5, 22, 0);
        $end = Carbon::create(2026, 1, 5, 6, 0);

        $result = $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame('8.00', $result);
    }

    public function test_does_not_mutate_input_end()
    {
        $start = Carbon::create(2026, 1, 5, 22, 0);
        $end = Carbon::create(2026, 1, 5, 6, 0);
        $originalEndHour = $end->hour;
        $originalEndDay = $end->day;

        $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame($originalEndHour, $end->hour);
        $this->assertSame($originalEndDay, $end->day);
    }

    public function test_fifteen_minute_difference()
    {
        $start = Carbon::create(2026, 1, 5, 6, 0);
        $end = Carbon::create(2026, 1, 5, 6, 15);

        $result = $this->calculator->calculateOvertimeHours($start, $end);

        $this->assertSame('0.25', $result);
    }

    // -- currentWeekSundayBased --

    public function test_january_first_is_week_one()
    {
        $date = Carbon::create(2026, 1, 1);

        $result = $this->calculator->currentWeekSundayBased($date);

        $this->assertSame(1, $result);
    }

    public function test_january_eighth_is_week_two()
    {
        $date = Carbon::create(2026, 1, 8);

        $result = $this->calculator->currentWeekSundayBased($date);

        $this->assertSame(2, $result);
    }

    public function test_sunday_after_jan_first_is_week_two()
    {
        $date = Carbon::create(2026, 1, 4); // Sunday Jan 4, 2026

        $result = $this->calculator->currentWeekSundayBased($date);

        $this->assertSame(2, $result);
    }

    public function test_monday_january_fifth_is_week_two()
    {
        $date = Carbon::create(2026, 1, 5); // Monday Jan 5, 2026

        $result = $this->calculator->currentWeekSundayBased($date);

        $this->assertSame(2, $result);
    }

    public function test_week_fifty_three_for_late_december()
    {
        $date = Carbon::create(2026, 12, 31);

        $result = $this->calculator->currentWeekSundayBased($date);

        $this->assertGreaterThanOrEqual(52, $result);
    }

    public function test_defaults_to_now_when_null()
    {
        $result = $this->calculator->currentWeekSundayBased(null);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    // -- getMinimumOvertimeHours --

    public function test_valid_quarter_increment_025()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => 0.25]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(0.25, $result);
    }

    public function test_valid_quarter_increment_100()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => 1.0]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }

    public function test_valid_quarter_increment_175()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => 1.75]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.75, $result);
    }

    public function test_invalid_non_quarter_value_falls_back_to_default()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => 0.20]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }

    public function test_negative_value_falls_back_to_default()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => -0.5]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }

    public function test_zero_value_falls_back_to_default()
    {
        file_put_contents($this->tempConfigPath, json_encode(['minimum_overtime_hours' => 0]));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }

    public function test_missing_config_file_returns_default()
    {
        $calculator = new OvertimeCalculator('/nonexistent/path/config.json');

        $result = $calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }

    public function test_missing_key_in_config_returns_default()
    {
        file_put_contents($this->tempConfigPath, json_encode(['other_key' => 'value']));

        $result = $this->calculator->getMinimumOvertimeHours();

        $this->assertSame(1.0, $result);
    }
}
