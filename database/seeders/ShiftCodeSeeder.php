<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftCodeSeeder extends Seeder
{
    public function run(): void
    {
        $startTimes = ['06:00', '07:00', '08:00', '09:00', '10:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
        $endTimes = ['14:00', '15:00', '16:00', '17:00', '18:00', '22:00', '00:00', '02:00', '04:00', '06:00'];

        for ($i = 1; $i <= 50; $i++) {
            $letter = chr(65 + (($i - 1) % 26));
            $suffix = floor(($i - 1) / 26);

            $code = $suffix > 0 ? "{$letter}{$suffix}" : (string) $letter;

            Shift::firstOrCreate(
                ['code' => $code],
                [
                    'start_time' => $startTimes[array_rand($startTimes)],
                    'end_time' => $endTimes[array_rand($endTimes)],
                ]
            );
        }
    }
}
