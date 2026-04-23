<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class OvertimeRequestSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Need to finish pending deliverables for the sprint.',
            'Client requested urgent bug fix deployment.',
            'End-of-month reporting and data reconciliation.',
            'System migration tasks require after-hours work.',
            'Team knowledge sharing session preparation.',
            'Unexpected production issue needs immediate resolution.',
            'Quarterly audit documentation completion.',
        ];

        $schedules = Schedule::with('shift')
            ->whereIn('week', [17, 18])
            ->whereNotNull('shift_id')
            ->orderBy('week')
            ->orderBy('date')
            ->get();

        if ($schedules->isEmpty()) {
            $this->command->warn('No schedules found for weeks 17 or 18. Skipping overtime request seeding.');
            return;
        }

        $count = 0;

        foreach ($schedules as $schedule) {
            $shift = $schedule->shift;
            $shiftEnd = $shift->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $shift->end_time) : null;

            $isNightShift = $shiftEnd && $shift->start_time && $shiftEnd->lessThan(
                \Carbon\Carbon::createFromFormat('H:i:s', $shift->start_time)
            );

            if ($shiftEnd && !$isNightShift) {
                $otStart = $shiftEnd->copy()->addMinutes(30);
                $otEnd = $otStart->copy()->addHours(2);
            } else {
                $otStart = \Carbon\Carbon::createFromTimeString('18:00:00');
                $otEnd = \Carbon\Carbon::createFromTimeString('20:00:00');
            }

            $status = $schedule->week === 17 ? 'PENDING' : 'APPROVED';

            OvertimeRequest::firstOrCreate(
                ['employee_schedule_id' => $schedule->id, 'status' => $status],
                [
                    'start_time' => $otStart->format('H:i:s'),
                    'end_time' => $otEnd->format('H:i:s'),
                    'hours' => $otStart->diffInMinutes($otEnd) / 60,
                    'reason' => $reasons[array_rand($reasons)],
                ]
            );

            $count++;
        }

        $this->command->info("Seeded {$count} overtime request(s): week 17 = PENDING, week 18 = APPROVED.");
    }
}
