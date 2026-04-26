<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftCodeSeeder extends Seeder
{
    public function run(): void
    {
        $shiftCodes = [
            ['code' => 'AA', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['code' => 'AB', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['code' => 'AC', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
            ['code' => 'BA', 'start_time' => '07:00:00', 'end_time' => '15:00:00'],
            ['code' => 'BB', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
            ['code' => 'BC', 'start_time' => '09:00:00', 'end_time' => '18:00:00'],
            ['code' => 'CA', 'start_time' => '05:00:00', 'end_time' => '13:00:00'],
            ['code' => 'CB', 'start_time' => '13:00:00', 'end_time' => '21:00:00'],
            ['code' => 'CC', 'start_time' => '21:00:00', 'end_time' => '05:00:00'],
            ['code' => 'DA', 'start_time' => '06:30:00', 'end_time' => '14:30:00'],
            ['code' => 'DB', 'start_time' => '10:00:00', 'end_time' => '19:00:00'],
            ['code' => 'DC', 'start_time' => '15:00:00', 'end_time' => '23:00:00'],
            ['code' => 'EA', 'start_time' => '07:30:00', 'end_time' => '16:00:00'],
            ['code' => 'EB', 'start_time' => '16:00:00', 'end_time' => '00:00:00'],
            ['code' => 'EC', 'start_time' => '00:00:00', 'end_time' => '08:00:00'],
            ['code' => 'FA', 'start_time' => '08:30:00', 'end_time' => '17:30:00'],
            ['code' => 'FB', 'start_time' => '11:00:00', 'end_time' => '20:00:00'],
            ['code' => 'FC', 'start_time' => '20:00:00', 'end_time' => '04:00:00'],
            ['code' => 'GA', 'start_time' => '05:30:00', 'end_time' => '13:30:00'],
            ['code' => 'GB', 'start_time' => '09:30:00', 'end_time' => '18:30:00'],
        ];

        foreach ($shiftCodes as $shiftCode) {
            Shift::firstOrCreate(
                ['code' => $shiftCode['code']],
                $shiftCode
            );
        }

        $this->command->info('Shift codes seeded: ' . count($shiftCodes) . ' codes.');
    }
}
