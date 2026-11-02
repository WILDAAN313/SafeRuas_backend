<?php

namespace Database\Factories;

use App\Models\Instansi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instansi>
 */
class InstansiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_instansi' => fake()->company(),
            'alamat' => fake()->address(),
            'telepon' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
        ];
    }
}
