<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    public function definition(): array
    {
        return ['material_id' => Material::factory(), 'buyer_id' => User::factory(), 'message' => fake()->paragraph(), 'status' => 'pending'];
    }
}
