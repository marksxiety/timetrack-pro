<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class OvertimeCalculator
{
    public function calculateOvertimeHours(Carbon $start, Carbon $end): string
    {
        $adjustedEnd = $end->copy();

        if ($adjustedEnd->lessThan($start)) {
            $adjustedEnd->addDay();
        }

        $minutes = $start->diffInMinutes($adjustedEnd);
        $decimalHours = $minutes / 60;

        return number_format($decimalHours, 2);
    }

    public function currentWeekSundayBased($date = null): int
    {
        $date = $date ?: Carbon::now();
        $firstDayOfYear = Carbon::create($date->year, 1, 1);

        $pastDays = $firstDayOfYear->diffInDays($date);

        $weekNumber = (int) ceil(($pastDays + $firstDayOfYear->dayOfWeek + 1) / 7);

        return $weekNumber;
    }

    public function getMinimumOvertimeHours(): float
    {
        $value = (float) (Setting::get('minimum_overtime_hours', 1));

        if ($value <= 0 || abs(fmod($value, 0.25)) > 0.001) {
            return 1.0;
        }

        return $value;
    }
}
