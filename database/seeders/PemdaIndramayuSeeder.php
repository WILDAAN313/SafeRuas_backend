<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

class PemdaIndramayuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Instansi::query()->firstOrCreate([
            'nama_instansi' => Instansi::NAMA_PEMDA_INDRAMAYU,
        ]);
    }
}
