<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationUnitFactory extends Factory
{
    protected $model = \App\Models\OrganizationUnit::class;

    public function definition(): array
    {
        return [
            'unit_path' => fake()->unique()->company(),
        ];
    }
}
