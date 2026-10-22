<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'category_id' => Category::factory(), 'subject_id' => Subject::factory(), 'title' => fake()->sentence(4), 'description' => fake()->paragraph(), 'semester' => fake()->numberBetween(1, 8), 'price' => fake()->numberBetween(0, 500), 'condition' => 'good', 'status' => 'available'];
    }
}
