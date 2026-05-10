<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class OvertimeTimeValidationService
{
    public function __construct(
        private OvertimeCalculator $calculator,
        private OvertimeOverlapValidator $overlapValidator,
    ) {}

    public function validate(array $data): array
    {
        $rules = [
            'employee_schedule_id' => 'exists:schedules,id|required',
            'date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ];

        try {
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return ['valid' => false, 'errors' => $validator->errors()->messages()];
            }
        } catch (\Throwable) {
            return ['valid' => false, 'errors' => ['_general' => ['Invalid data format.']]];
        }

        $date = $data['date'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        try {
            $submittedStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . trim($startTime));
            $submittedEnd   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . trim($endTime));
        } catch (\Throwable) {
            return ['valid' => false, 'errors' => ['_general' => ['Invalid date or time format.']]];
        }

        try {
            $schedule = Schedule::with('shift')->findOrFail($data['employee_schedule_id']);
        } catch (\Throwable) {
            return ['valid' => false, 'errors' => ['_general' => ['Schedule not found.']]];
        }

        $shift = $schedule->shift;

        $validation = $this->overlapValidator->validate(
            $submittedStart,
            $submittedEnd,
            $shift?->start_time,
            $shift?->end_time,
            $date
        );

        if (!$validation['valid']) {
            return ['valid' => false, 'errors' => $validation['errors']];
        }

        $submittedStart = $validation['start'];
        $submittedEnd = $validation['end'];

        $hours = $this->calculator->calculateOvertimeHours($submittedStart, $submittedEnd);

        $minimumHours = $this->calculator->getMinimumOvertimeHours();
        if ((float) $hours < $minimumHours) {
            return [
                'valid' => false,
                'errors' => [
                    'start_time' => ["Overtime request must be at least {$minimumHours} hour(s)."],
                    'end_time' => ["Overtime request must be at least {$minimumHours} hour(s)."],
                ],
            ];
        }

        return [
            'valid' => true,
            'hours' => $hours,
            'schedule' => $schedule,
        ];
    }
}
