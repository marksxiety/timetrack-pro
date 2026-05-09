<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OrganizationUnitSeeder::class);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        Setting::firstOrCreate(
            ['key' => 'default_shift_codes'],
            ['value' => json_encode(array_map(fn($day) => ['day' => $day, 'code' => ''], $days))],
        );

        Setting::firstOrCreate(
            ['key' => 'minimum_overtime_hours'],
            ['value' => '1'],
        );

        Setting::firstOrCreate(
            ['key' => 'overtime_minute_step'],
            ['value' => '15'],
        );

        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'employeeid' => env('ADMIN_EMPLOYEEID', 'ADMIN001'),
                'role' => 'admin',
                'organization_unit_id' => null,
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme')),
                'active' => true,
            ],
        );
    }
}
