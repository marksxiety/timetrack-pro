<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableForeignKeyChecks();

        $this->command->info('Seeding organization units...');
        $this->call(OrganizationUnitSeeder::class);

        $this->command->info('Seeding shift codes...');
        $this->call(ShiftCodeSeeder::class);

        $shiftCodes = Shift::all();
        if ($shiftCodes->isEmpty()) {
            $this->command->error('No shift codes found. Ensure ShiftCodeSeeder ran successfully.');
            DB::enableForeignKeyChecks();
            return;
        }

        $orgUnit = OrganizationUnit::first();

        $this->command->info('Seeding users...');
        $users = $this->getOrCreateUsers($orgUnit);

        $years = range(2022, 2026);
        $totalCreated = 0;

        foreach ($years as $year) {
            $this->command->info("Seeding schedules for {$year}...");
            $count = $this->seedForYear($year, $users, $shiftCodes);
            $totalCreated += $count;
        }

        $this->command->info("Done! Total schedules created: {$totalCreated}");

        DB::enableForeignKeyChecks();
    }

    private function getOrCreateUsers(OrganizationUnit $orgUnit): \Illuminate\Database\Eloquent\Collection
    {
        $existingCount = User::count();
        if ($existingCount >= 20) {
            return User::where('active', true)->inRandomOrder()->limit(30)->get();
        }

        $users = collect();
        for ($i = 1; $i <= 25; $i++) {
            $employeeId = "SEED{$i}";
            $users->push(User::firstOrCreate(
                ['employeeid' => $employeeId],
                [
                    'name' => fake()->name(),
                    'email' => "seeder{$i}@example.com",
                    'role' => $i <= 2 ? 'admin' : ($i <= 5 ? 'approver' : 'employee'),
                    'organization_unit_id' => $orgUnit->id,
                    'password' => bcrypt('password'),
                    'active' => true,
                ]
            ));
        }

        return $users;
    }

    private function seedForYear(int $year, $users, $shiftCodes): int
    {
        $created = 0;
        $startOfYear = Carbon::create($year, 1, 1);
        $endOfYear = Carbon::create($year, 12, 31);

        foreach ($users as $user) {
            $assignedShift = $shiftCodes->random();

            for ($date = $startOfYear->copy(); $date->lte($endOfYear); $date->addDay()) {
                if (!$date->isWeekday()) {
                    continue;
                }

                if (rand(1, 100) <= 15) {
                    $assignedShift = $shiftCodes->random();
                }

                $week = min((int) $date->isoWeek(), 52);

                $exists = Schedule::where('user_id', $user->id)
                    ->where('date', $date->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                if (rand(1, 100) <= 10) {
                    continue;
                }

                Schedule::create([
                    'user_id' => $user->id,
                    'shift_id' => $assignedShift->id,
                    'week' => $week,
                    'date' => $date->toDateString(),
                    'created_at' => $date->copy()->subDays(rand(1, 14)),
                    'updated_at' => $date->copy()->subDays(rand(0, 3)),
                ]);

                $created++;
            }
        }

        return $created;
    }
}
