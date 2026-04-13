<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class OrganizationUnitSeeder extends Seeder
{
    public function run(): void
    {
        OrganizationUnit::firstOrCreate([
            'unit_path' => 'Default',
        ]);
    }
}
