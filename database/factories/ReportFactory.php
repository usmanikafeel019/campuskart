<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return ['material_id' => Material::factory(), 'reporter_id' => User::factory(), 'reason' => fake()->paragraph(), 'status' => 'pending'];
    }
}
