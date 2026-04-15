<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftCodeSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['code' => 'AA', 'start_time' => null, 'end_time' => null],
            ['code' => 'BB', 'start_time' => null, 'end_time' => null],
            ['code' => 'CC', 'start_time' => '08:00', 'end_time' => '17:00'],
            ['code' => 'DD', 'start_time' => '08:00', 'end_time' => '17:00'],
            ['code' => 'EE', 'start_time' => '08:00', 'end_time' => '17:00'],
            ['code' => 'FF', 'start_time' => '08:00', 'end_time' => '17:00'],
            ['code' => 'GG', 'start_time' => '08:00', 'end_time' => '17:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(
                ['code' => $shift['code']],
                ['start_time' => $shift['start_time'], 'end_time' => $shift['end_time']]
            );
        }
    }
}
