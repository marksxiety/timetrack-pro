<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use App\Models\RequiredHours;
use Illuminate\Database\Seeder;

class RequiredHoursSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            RequiredHours::firstOrCreate(
                [
                    'year' => 2024 + floor(($i - 1) / 52),
                    'week' => (($i - 1) % 52) + 1,
                ],
                [
                    'required_hours' => rand(32, 48),
                    'organization_unit_id' => OrganizationUnit::first()?->id,
                ]
            );
        }
    }
}
