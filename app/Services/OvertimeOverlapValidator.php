<?php

namespace App\Services;

use Carbon\Carbon;

class OvertimeOverlapValidator
{
    public function validate(Carbon $submittedStart, Carbon $submittedEnd, ?string $shiftStartTime, ?string $shiftEndTime, string $date): array
    {
        $hasShiftTimes = $shiftStartTime !== null && $shiftEndTime !== null;

        if (!$hasShiftTimes) {
            return [
                'valid' => true,
                'start' => $submittedStart,
                'end' => $submittedEnd,
            ];
        }

        $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $shiftStartTime);
        $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $shiftEndTime);

        $isNightShift = $shiftEnd->lessThan($shiftStart);

        if ($submittedEnd->equalTo($submittedStart)) {
            return [
                'valid' => false,
                'errors' => [
                    'end_time' => 'End time must be after start time.',
                ],
            ];
        }

        if ($isNightShift) {
            $shiftEnd = $shiftEnd->copy()->addDay();

            if ($submittedStart->hour < 12) {
                $submittedStart = $submittedStart->copy()->addDay();
                $submittedEnd = $submittedEnd->copy()->addDay();
            }

            if ($submittedEnd->lessThanOrEqualTo($submittedStart)) {
                return [
                    'valid' => false,
                    'errors' => [
                        'end_time' => 'End time must be after start time.',
                    ],
                ];
            }
        } else {
            if ($submittedEnd->lessThan($submittedStart)) {
                $submittedEnd = $submittedEnd->copy()->addDay();
            }
        }

        $isBeforeShift = $submittedEnd->lessThanOrEqualTo($shiftStart);
        $isAfterShift = $submittedStart->greaterThanOrEqualTo($shiftEnd);

        if (!$isBeforeShift && !$isAfterShift) {
            return [
                'valid' => false,
                'errors' => [
                    'start_time' => 'Overtime must be entirely before or after the scheduled shift.',
                    'end_time' => 'Overtime must be entirely before or after the scheduled shift.',
                ],
            ];
        }

        return [
            'valid' => true,
            'start' => $submittedStart,
            'end' => $submittedEnd,
        ];
    }
}
