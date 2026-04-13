<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OrganizationUnitSeeder::class);
        $this->call(ShiftCodeSeeder::class);
    }
}
