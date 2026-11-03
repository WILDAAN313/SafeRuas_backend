<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminStatistikTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_statistics_show_database_counts_for_the_two_categories_and_completed_work(): void
    {
        $instansi = Instansi::factory()->create();
        $admin = User::factory()->for($instansi, 'instansi')->create(['role' => 'admin_instansi']);

        Laporan::factory()->for($instansi, 'instansi')->create([
            'kategori' => 'Kerusakan Jalan',
            'status' => 'menunggu',
        ]);
        Laporan::factory()->for($instansi, 'instansi')->create([
            'kategori' => 'Kerusakan Jalan',
            'status' => 'diproses',
        ]);
        Laporan::factory()->for($instansi, 'instansi')->create([
            'kategori' => 'Kerusakan Jalan',
            'status' => 'selesai',
        ]);
        Laporan::factory()->for($instansi, 'instansi')->create([
            'kategori' => 'Kerusakan Rambu',
            'status' => 'selesai',
        ]);

        Laporan::factory()->create([
            'kategori' => 'Kerusakan Jalan',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.statistik.index'));

        $response->assertOk()
            ->assertViewHas('totalLaporan', 4)
            ->assertViewHas('jumlahMenunggu', 1)
            ->assertViewHas('jumlahDiproses', 1)
            ->assertViewHas('jumlahSelesai', 2)
            ->assertViewHas('persentaseSelesai', 50.0)
            ->assertViewHas('kategoriStatistik', function ($statistics): bool {
                return $statistics->map(fn (object $row): array => [
                    $row->kategori,
                    $row->total,
                    $row->menunggu,
                    $row->diproses,
                    $row->selesai,
                ])->all() === [
                    ['Kerusakan Jalan', 3, 1, 1, 1],
                    ['Kerusakan Rambu', 1, 0, 0, 1],
                ];
            })
            ->assertSee('Ringkasan Kinerja Unit')
            ->assertSee('tidak menjadi bukti bahwa perbaikan fisik telah diverifikasi');
    }
}
