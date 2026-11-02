<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SuperAdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Laporan dummy hanya boleh dibuat pada lingkungan local atau testing.');
        }

        $createdCount = DB::transaction(function (): int {
            $pelapor = User::query()->firstOrCreate(['email' => 'warga.peta.demo@saferuas.test'], [
                'name' => 'Warga Demo Peta',
                'role' => 'masyarakat',
                'password' => Str::random(40),
                'is_active' => true,
            ]);

            if ($pelapor->role !== 'masyarakat' || $pelapor->name !== 'Warga Demo Peta') {
                throw new RuntimeException('Email pelapor demo sudah digunakan oleh akun lain.');
            }

            $instansi = Instansi::query()->firstOrCreate(['nama_instansi' => 'Dinas Pekerjaan Umum Demo'], [
                'alamat' => 'Alamat simulasi, Kabupaten Indramayu',
                'email' => 'pu.demo@saferuas.test',
            ]);

            $reports = [
                [
                    'judul' => '[DUMMY] Jalan berlubang - Titik A',
                    'kategori' => 'Jalan rusak',
                    'status' => 'menunggu',
                    'latitude' => -6.3276,
                    'longitude' => 108.3249,
                    'alamat_lokasi' => 'Lokasi simulasi A, Kabupaten Indramayu',
                ],
                [
                    'judul' => '[DUMMY] Lampu jalan mati - Titik B',
                    'kategori' => 'Lampu jalan',
                    'status' => 'diproses',
                    'latitude' => -6.4466,
                    'longitude' => 108.3103,
                    'alamat_lokasi' => 'Lokasi simulasi B, Kabupaten Indramayu',
                ],
                [
                    'judul' => '[DUMMY] Drainase tersumbat - Titik C',
                    'kategori' => 'Drainase',
                    'status' => 'selesai',
                    'latitude' => -6.3998,
                    'longitude' => 108.1522,
                    'alamat_lokasi' => 'Lokasi simulasi C, Kabupaten Indramayu',
                ],
            ];

            $created = 0;

            foreach ($reports as $data) {
                $report = Laporan::query()->firstOrCreate([
                    'user_id' => $pelapor->id,
                    'judul' => $data['judul'],
                ], [
                    ...$data,
                    'instansi_id' => $instansi->id,
                    'deskripsi' => 'Data dummy untuk menguji peta, filter status, dan detail laporan Superadmin. Bukan laporan kerusakan nyata.',
                    'foto' => null,
                ]);

                $created += (int) $report->wasRecentlyCreated;
            }

            return $created;
        });

        $this->command?->info("{$createdCount} laporan dummy baru ditambahkan. Data demo yang sudah ada tetap dipertahankan.");
    }
}
