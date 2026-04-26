<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OvertimeRequestSeeder extends Seeder
{
    private const STATUSES = [
        'PENDING',
        'APPROVED',
        'FILED',
        'DECLINED',
        'DISAPPROVED',
        'CANCELED',
    ];

    private const REASONS = [
        'Project deadline approaching',
        'End of month reporting',
        'System deployment',
        'Client meeting preparation',
        'Bug fixing and urgent patches',
        'Data migration tasks',
        'Year-end closing activities',
        'Training new team members',
        'Documentation updates',
        'Performance review preparations',
        'Urgent client request',
        'Quarterly audit preparation',
        'Infrastructure maintenance',
        'Database optimization',
        'Code review backlog',
        'Release preparation',
        'Compliance requirements',
        'Security patch deployment',
        'Integration testing',
        'Emergency incident response',
        'Sprint deliverables',
        'Vendor coordination',
        'System upgrade',
        'Backup and recovery testing',
        'User acceptance testing support',
    ];

    private const REMARKS_MAP = [
        'DISAPPROVED' => [
            'Overtime not justified for this task',
            'Please plan better next time',
            'Budget constraints - overtime not approved',
            'Task should be completed within regular hours',
            'Not aligned with department priorities',
            'Exceeds approved overtime budget',
        ],
        'CANCELED' => [
            'No longer needed',
            'Task completed early',
            'Schedule changed',
            'Personal emergency',
            'Requirement changed',
            'Shift coverage no longer required',
        ],
        'DECLINED' => [
            'Decided not to pursue overtime',
            'Alternative solution found',
            'Priority changed',
            'Resource conflict resolved',
            'Completed within regular hours',
            'Scope reduced',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding organization units...');
        $this->call(OrganizationUnitSeeder::class);

        $this->command->info('Seeding shift codes...');
        $this->call(ShiftCodeSeeder::class);

        $shiftCodes = Shift::all();
        if ($shiftCodes->isEmpty()) {
            $this->command->error('No shift codes found. Ensure ShiftCodeSeeder ran successfully.');
            return;
        }

        $orgUnit = OrganizationUnit::first();

        $this->command->info('Seeding users...');
        $users = $this->getOrCreateUsers($orgUnit);

        $years = range(2022, 2026);
        $requestsPerYear = 200;
        $totalCreated = 0;

        foreach ($years as $year) {
            $this->command->info("Seeding {$requestsPerYear} overtime requests for {$year}...");
            $count = $this->seedForYear($year, $requestsPerYear, $users, $shiftCodes);
            $totalCreated += $count;
        }

        $this->command->info("Done! Total overtime requests created: {$totalCreated}");
    }

    private function getOrCreateUsers(OrganizationUnit $orgUnit): \Illuminate\Database\Eloquent\Collection
    {
        $existingCount = User::count();
        if ($existingCount >= 20) {
            return User::where('active', true)->inRandomOrder()->limit(30)->get();
        }

        $users = [];
        for ($i = 1; $i <= 25; $i++) {
            $employeeId = "SEED{$i}";
            $users[] = User::firstOrCreate(
                ['employeeid' => $employeeId],
                [
                    'name' => fake()->name(),
                    'email' => "seeder{$i}@example.com",
                    'role' => $i <= 2 ? 'admin' : ($i <= 5 ? 'approver' : 'employee'),
                    'organization_unit_id' => $orgUnit->id,
                    'password' => bcrypt('password'),
                    'active' => true,
                ]
            );
        }

        return collect($users);
    }

    private function seedForYear(int $year, int $count, $users, $shiftCodes): int
    {
        $created = 0;
        $dates = $this->generateRandomDates($year, $count);
        $usedKeys = [];

        foreach ($dates as $dateStr) {
            $date = Carbon::parse($dateStr);
            $user = $users->random();
            $shift = $shiftCodes->random();

            $uniqueKey = "{$user->id}-{$shift->id}-{$dateStr}";
            if (in_array($uniqueKey, $usedKeys)) {
                continue;
            }
            $usedKeys[] = $uniqueKey;

            $week = $this->getIsoWeek($date);

            $schedule = Schedule::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'week' => $week,
                    'created_at' => $date->copy()->subDays(rand(1, 14)),
                    'updated_at' => $date->copy()->subDays(rand(0, 3)),
                ]
            );

            $status = self::STATUSES[array_rand(self::STATUSES)];
            $hours = round(rand(10, 50) / 10, 1);

            $shiftEnd = Carbon::parse($shift->end_time);
            $otStart = $shiftEnd->copy()->addMinutes(rand(0, 30));
            $otEnd = $otStart->copy()->addHours($hours);

            $reason = self::REASONS[array_rand(self::REASONS)];
            $remarks = null;

            if (isset(self::REMARKS_MAP[$status])) {
                $remarksList = self::REMARKS_MAP[$status];
                $remarks = $remarksList[array_rand($remarksList)];
            }

            $createdAt = $date->copy()->addHours(rand(8, 20));
            $updatedAt = $createdAt->copy()->addHours(rand(1, 72));

            try {
                OvertimeRequest::create([
                    'employee_schedule_id' => $schedule->id,
                    'start_time' => $otStart->format('H:i:s'),
                    'end_time' => $otEnd->format('H:i:s'),
                    'hours' => $hours,
                    'reason' => $reason,
                    'status' => $status,
                    'remarks' => $remarks,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
                $created++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $created;
    }

    private function generateRandomDates(int $year, int $count): array
    {
        $dates = [];
        $startOfYear = Carbon::create($year, 1, 1);
        $endOfYear = Carbon::create($year, 12, 31);
        $totalDays = $startOfYear->diffInDays($endOfYear);

        for ($i = 0; $i < $count; $i++) {
            $randomDay = rand(0, $totalDays);
            $dates[] = $startOfYear->copy()->addDays($randomDay)->toDateString();
        }

        sort($dates);

        return array_values(array_unique($dates));
    }

    private function getIsoWeek(Carbon $date): int
    {
        return min((int) $date->isoWeek(), 52);
    }
}
