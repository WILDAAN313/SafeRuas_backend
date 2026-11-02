<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminMasyarakatTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_view_citizens(): void
    {
        $this->get(route('superadmin.masyarakat.index'))->assertRedirectToRoute('login');
    }

    #[TestWith(['admin_instansi'])]
    #[TestWith(['developer'])]
    #[TestWith(['masyarakat'])]
    #[TestWith(['petugas'])]
    public function test_other_roles_cannot_view_citizen_data(string $role): void
    {
        $actor = User::factory()->create(['role' => $role]);
        $citizen = User::factory()->create(['role' => 'masyarakat']);

        $this->actingAs($actor)->getJson(route('superadmin.masyarakat.show', $citizen))->assertForbidden();
    }

    #[TestWith(['Siti Warga'])]
    #[TestWith(['siti.warga@saferuas.test'])]
    public function test_search_finds_citizens_by_name_or_email_and_shows_report_counts(string $search): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create([
            'role' => 'masyarakat', 'name' => 'Siti Warga', 'email' => 'siti.warga@saferuas.test',
        ]);
        Laporan::factory()->count(2)->for($citizen, 'pelapor')->create();
        User::factory()->create(['role' => 'admin_instansi', 'name' => 'Siti Warga Admin']);
        User::factory()->create(['role' => 'masyarakat', 'name' => 'Warga Lain']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.index', ['cari' => $search]))
            ->assertOk()->assertSee($citizen->email)->assertDontSee('Siti Warga Admin')->assertDontSee('Warga Lain')
            ->assertViewHas('masyarakat', fn ($citizens): bool => $citizens->total() === 1
                && $citizens->first()->is($citizen) && $citizens->first()->laporan_count === 2);
    }

    public function test_list_paginates_and_preserves_search(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        User::factory()->count(16)->create(['role' => 'masyarakat', 'name' => 'Warga Indramayu']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.index', ['cari' => 'Indramayu']))
            ->assertOk()->assertViewHas('masyarakat', fn ($citizens): bool => $citizens->total() === 16 && $citizens->count() === 15)
            ->assertSee('cari=Indramayu&amp;page=2', false);
    }

    public function test_empty_search_result_is_explained(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.index'))
            ->assertOk()->assertSee('Belum ada masyarakat yang sesuai dengan pencarian.');
    }

    public function test_invalid_search_is_rejected_before_rendering(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->getJson(route('superadmin.masyarakat.index', ['cari' => ['unexpected']]))
            ->assertUnprocessable()->assertJsonValidationErrors(['cari']);
    }

    public function test_detail_only_contains_the_selected_citizens_reports_and_links_to_read_only_details(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);
        $report = Laporan::factory()->for($citizen, 'pelapor')->for(Instansi::factory(), 'instansi')->create([
            'judul' => 'Jalan Berlubang Sindang', 'kategori' => 'Jalan rusak', 'status' => 'diproses',
        ]);
        Laporan::factory()->create(['judul' => 'Laporan Milik Orang Lain']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', $citizen))
            ->assertOk()->assertSee($citizen->email)->assertSee('Jalan Berlubang Sindang')
            ->assertSee('Jalan rusak')->assertSee('Diproses')->assertSee($report->instansi->nama_instansi)
            ->assertSee(route('superadmin.laporan.show', $report))->assertDontSee('Laporan Milik Orang Lain')
            ->assertViewHas('laporan', fn ($reports): bool => $reports->total() === 1 && $reports->first()->is($report));
    }

    public function test_report_history_paginates_newest_first(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);
        $older = Laporan::factory()->for($citizen, 'pelapor')->create(['created_at' => '2026-01-01 00:00:00']);
        Laporan::factory()->count(10)->for($citizen, 'pelapor')->create(['created_at' => '2026-01-02 00:00:00']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', [$citizen, 'page' => 2]))
            ->assertOk()->assertViewHas('laporan', fn ($reports): bool => $reports->total() === 11
                && $reports->count() === 1 && $reports->first()->is($older));
    }

    public function test_citizen_without_reports_has_an_empty_history(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', $citizen))
            ->assertOk()->assertSee('Pengguna ini belum membuat laporan.');
    }

    public function test_detail_escapes_citizen_and_report_content(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat', 'name' => '<script>alert("warga")</script>']);
        $instansi = Instansi::factory()->create(['nama_instansi' => '<img src=x onerror=alert(1)>']);
        $report = Laporan::factory()->for($citizen, 'pelapor')->for($instansi, 'instansi')->create([
            'judul' => '<script>alert("laporan")</script>', 'kategori' => '<b>Kategori</b>',
        ]);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', $citizen))
            ->assertOk()->assertSee($citizen->name)->assertDontSee($citizen->name, false)
            ->assertSee($report->judul)->assertDontSee($report->judul, false)
            ->assertSee($report->kategori)->assertDontSee($report->kategori, false)
            ->assertSee($instansi->nama_instansi)->assertDontSee($instansi->nama_instansi, false);
    }

    public function test_missing_citizen_returns_404(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', 999999))->assertNotFound();
    }

    public function test_an_admin_cannot_be_opened_as_a_citizen(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);

        $this->actingAs($superadmin)->get(route('superadmin.masyarakat.show', $admin))->assertNotFound();
    }

    #[TestWith(['POST'])]
    #[TestWith(['PUT'])]
    #[TestWith(['DELETE'])]
    public function test_citizen_monitoring_exposes_no_write_endpoint(string $method): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);

        $this->actingAs($superadmin)->json($method, route('superadmin.masyarakat.show', $citizen), ['name' => 'Diubah'])
            ->assertStatus(405);

        $this->assertDatabaseHas('users', ['id' => $citizen->id, 'name' => $citizen->name]);
    }
}
