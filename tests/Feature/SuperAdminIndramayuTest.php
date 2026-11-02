<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use App\Support\IndramayuBoundary;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminIndramayuTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    #[TestWith([-6.3276, 108.3249, true])]
    #[TestWith([-6.4466, 108.3103, true])]
    #[TestWith([-6.3998, 108.1522, true])]
    #[TestWith([-5.929748, 108.3796155, true])]
    #[TestWith([-5.8685165, 108.4059599, true])]
    #[TestWith([-5.8066741, 108.4554821, true])]
    #[TestWith([-6.2214914, 108.1988266, true])]
    #[TestWith([-6.1, 108.2, false])]
    #[TestWith([-6.26, 108.4, false])]
    #[TestWith([-6.8, 108.6, false])]
    public function test_big_boundary_recognizes_indramayu_mainland_and_islands(float $latitude, float $longitude, bool $expected): void
    {
        $this->assertSame($expected, app(IndramayuBoundary::class)->contains($latitude, $longitude));
    }

    public function test_map_excludes_outside_reports_even_when_the_viewport_covers_the_whole_world(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $inside = Laporan::factory()->create();
        $outside = Laporan::factory()->outsideIndramayu()->create(['alamat_lokasi' => 'Indramayu']);
        $sea = Laporan::factory()->create(['latitude' => -6.26, 'longitude' => 108.4]);

        $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta', [
            'south' => -90, 'north' => 90, 'west' => -180, 'east' => 180,
        ]))->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $inside->id)
            ->assertJsonPath('meta.total', 1)->assertJsonPath('bounds', [[-6.3276, 108.3249], [-6.3276, 108.3249]]);

        $this->assertModelExists($outside);
        $this->assertModelExists($sea);
    }

    public function test_map_without_viewport_still_enforces_the_administrative_boundary(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Laporan::factory()->outsideIndramayu()->create();

        $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'))->assertOk()
            ->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0)->assertJsonPath('bounds', null);
    }

    public function test_outside_viewport_cannot_reveal_reports_outside_the_service_area(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Laporan::factory()->create();
        Laporan::factory()->outsideIndramayu()->create();

        $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta', [
            'south' => -7, 'north' => -6.7, 'west' => 108, 'east' => 109,
        ]))->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0)->assertJsonPath('bounds', null);
    }

    public function test_boundary_reports_are_included_in_map_data_and_details(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $edge = Laporan::factory()->create(['latitude' => -6.2214914, 'longitude' => 108.1988266]);

        $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $edge->id);
        $this->get(route('superadmin.laporan.show', $edge))->assertOk();
    }

    public function test_direct_detail_access_to_a_report_outside_indramayu_returns_404(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $outside = Laporan::factory()->outsideIndramayu()->create();

        $this->actingAs($superadmin)->get(route('superadmin.laporan.show', $outside))->assertNotFound();
    }

    public function test_dashboard_uses_the_same_region_for_totals_statuses_categories_and_recent_reports(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $inside = Laporan::factory()->create(['judul' => 'Laporan Dalam Wilayah', 'kategori' => 'Jalan rusak', 'status' => 'menunggu']);
        Laporan::factory()->outsideIndramayu()->create(['judul' => 'Laporan Luar Wilayah', 'kategori' => 'Kategori Luar', 'status' => 'selesai']);

        $this->actingAs($superadmin)->get(route('superadmin.dashboard'))->assertOk()
            ->assertViewHas('totalLaporan', 1)
            ->assertViewHas('statistikStatus', fn ($statuses): bool => $statuses->pluck('jumlah', 'status')->all() === ['menunggu' => 1])
            ->assertViewHas('kategoriList', fn ($categories): bool => $categories->all() === ['Jalan rusak'])
            ->assertViewHas('laporanTerbaru', fn ($reports): bool => $reports->count() === 1 && $reports->first()->is($inside))
            ->assertViewHas('regionBounds', [[-6.6639586, 107.8507495], [-5.8065381, 108.5426029]])
            ->assertSee('data-region-bounds=', false)->assertDontSee('Laporan Luar Wilayah');
    }

    public function test_report_list_applies_region_before_pagination_and_filter_options(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Laporan::factory()->count(16)->create(['kategori' => 'Jalan rusak']);
        Laporan::factory()->outsideIndramayu()->create(['kategori' => 'Kategori Luar', 'judul' => 'Jangan Ditampilkan']);

        $this->actingAs($superadmin)->get(route('superadmin.laporan.index'))->assertOk()
            ->assertViewHas('laporan', fn ($reports): bool => $reports->total() === 16 && $reports->count() === 15)
            ->assertViewHas('kategoriList', fn ($categories): bool => $categories->all() === ['Jalan rusak'])
            ->assertDontSee('Jangan Ditampilkan');
    }

    public function test_citizen_report_counts_and_history_only_include_indramayu_reports(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);
        $inside = Laporan::factory()->for($citizen, 'pelapor')->create();
        Laporan::factory()->outsideIndramayu()->for($citizen, 'pelapor')->create(['judul' => 'Riwayat Luar Wilayah']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.index'))->assertOk()
            ->assertViewHas('masyarakat', fn ($users): bool => $users->first()->laporan_count === 1);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', $citizen))->assertOk()
            ->assertViewHas('laporan', fn ($reports): bool => $reports->total() === 1 && $reports->first()->is($inside))
            ->assertDontSee('Riwayat Luar Wilayah');
    }

    public function test_general_report_queries_are_not_globally_changed_by_the_superadmin_region(): void
    {
        Laporan::factory()->create();
        Laporan::factory()->outsideIndramayu()->create();

        $this->assertSame(2, Laporan::query()->count());
        $this->assertSame(1, Laporan::query()->withinIndramayu()->count());
    }
}
