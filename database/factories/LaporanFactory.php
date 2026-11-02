<?php

namespace Database\Factories;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laporan>
 */
class LaporanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'instansi_id' => null,
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraph(),
            'kategori' => 'Jalan rusak',
            'foto' => null,
            'latitude' => -6.3276,
            'longitude' => 108.3249,
            'alamat_lokasi' => fake()->address(),
            'status' => 'menunggu',
        ];
    }

    public function outsideIndramayu(): static
    {
        return $this->state(fn (array $attributes): array => ['latitude' => -6.8, 'longitude' => 108.6]);
    }
}
