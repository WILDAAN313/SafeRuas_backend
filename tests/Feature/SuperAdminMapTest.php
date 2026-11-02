<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminMapTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_from_the_dashboard_to_login(): void
    {
        $this->get(route('superadmin.dashboard'))->assertRedirectToRoute('login');
    }

    public function test_map_data_returns_401_without_authentication(): void
    {
        $this->getJson(route('superadmin.laporan.peta'))->assertUnauthorized();
    }

    #[TestWith(['admin_instansi'])]
    #[TestWith(['masyarakat'])]
    #[TestWith(['petugas'])]
    #[TestWith(['developer'])]
    public function test_map_data_returns_403_for_roles_other_than_superadmin(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->getJson(route('superadmin.laporan.peta'));

        $response->assertForbidden();
    }

    public function test_admin_instansi_is_redirected_to_its_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin_instansi']);

        $response = $this->actingAs($user)->get(route('superadmin.dashboard'));

        $response->assertRedirectToRoute('admin.dashboard');
    }

    #[TestWith(['masyarakat'])]
    #[TestWith(['petugas'])]
    #[TestWith(['developer'])]
    public function test_dashboard_returns_403_for_other_roles(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get(route('superadmin.dashboard'));

        $response->assertForbidden();
    }

    public function test_dashboard_renders_the_map_and_regional_report_summary(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Laporan::factory()->create(['judul' => 'Lampu jalan padam', 'status' => 'diproses']);

        $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

        $response->assertOk()
            ->assertViewIs('superadmin.dashboard.index')
            ->assertViewHas('totalLaporan', 1)
            ->assertSee('Peta sebaran laporan')
            ->assertSee('Lampu jalan padam')
            ->assertSee(route('superadmin.laporan.peta'));
    }

    public function test_map_returns_reports_from_all_instansi_and_excludes_invalid_coordinates(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $first = Laporan::factory()->for(Instansi::factory(), 'instansi')->create([
            'latitude' => -6.3276,
            'longitude' => 108.3249,
        ]);
        $second = Laporan::factory()->for(Instansi::factory(), 'instansi')->create([
            'latitude' => -6.4466,
            'longitude' => 108.3103,
        ]);
        Laporan::factory()->create(['latitude' => 91]);
        Laporan::factory()->create(['longitude' => 181]);

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.0.latitude', -6.4466)
            ->assertJsonPath('data.0.longitude', 108.3103)
            ->assertJsonPath('data.0.instansi', $second->instansi->nama_instansi)
            ->assertJsonPath('data.0.url', route('superadmin.laporan.show', $second))
            ->assertJsonPath('data.1.id', $first->id)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.dibatasi', false)
            ->assertJsonPath('bounds', [[-6.4466, 108.3103], [-6.3276, 108.3249]]);
    }

    public function test_map_combines_status_and_category_filters(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $matching = Laporan::factory()->create(['status' => 'diproses', 'kategori' => 'Lampu jalan']);
        Laporan::factory()->create(['status' => 'menunggu', 'kategori' => 'Lampu jalan']);
        Laporan::factory()->create(['status' => 'diproses', 'kategori' => 'Jalan rusak']);

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta', [
            'status' => 'diproses',
            'kategori' => 'Lampu jalan',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_map_only_returns_reports_inside_the_requested_area_including_its_edges(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $inside = Laporan::factory()->create(['latitude' => -6.3276, 'longitude' => 108.3249]);
        $edge = Laporan::factory()->create(['latitude' => -6.4466, 'longitude' => 108.3103]);
        Laporan::factory()->create(['latitude' => -6.3998, 'longitude' => 108.1522]);
        Laporan::factory()->outsideIndramayu()->create();

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta', [
            'south' => -6.4466, 'north' => -6.32, 'west' => 108.3103, 'east' => 108.33,
        ]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $edge->id)
            ->assertJsonPath('data.1.id', $inside->id);
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  list<string>  $invalidFields
     */
    #[TestWith([['status' => 'invalid'], ['status']])]
    #[TestWith([['kategori' => ['unexpected']], ['kategori']])]
    #[TestWith([['south' => -7], ['west', 'north', 'east']])]
    #[TestWith([['south' => -91, 'north' => -6, 'west' => 106, 'east' => 107], ['south']])]
    #[TestWith([['south' => -7, 'north' => -8, 'west' => 106, 'east' => 107], ['north']])]
    #[TestWith([['south' => -7, 'north' => -6, 'west' => 106, 'east' => 105], ['east']])]
    #[TestWith([['south' => -7, 'north' => -6, 'west' => 106, 'east' => 181], ['east']])]
    public function test_map_returns_422_for_invalid_filters_or_bounds(array $query, array $invalidFields): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta', $query));

        $response->assertUnprocessable()->assertJsonValidationErrors($invalidFields);
    }

    public function test_empty_map_returns_no_bounds_and_an_empty_report_list(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'));

        $response->assertOk()->assertExactJson([
            'data' => [],
            'meta' => ['total' => 0, 'ditampilkan' => 0, 'dibatasi' => false],
            'bounds' => null,
        ]);
    }

    public function test_map_limits_pin_data_and_keeps_bounds_of_all_matching_reports(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $pelapor = User::factory()->create();
        Laporan::factory()->for($pelapor, 'pelapor')->create(['latitude' => -6.3998, 'longitude' => 108.1522]);
        Laporan::factory()->count(500)->for($pelapor, 'pelapor')->create(['latitude' => -6.3276, 'longitude' => 108.3249]);

        $response = $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'));

        $response->assertOk()
            ->assertJsonCount(500, 'data')
            ->assertJsonPath('meta.total', 501)
            ->assertJsonPath('meta.ditampilkan', 500)
            ->assertJsonPath('meta.dibatasi', true)
            ->assertJsonPath('bounds', [[-6.3998, 108.1522], [-6.3276, 108.3249]]);
    }

    #[TestWith(['superadmin.dashboard'])]
    #[TestWith(['superadmin.laporan.index'])]
    #[TestWith(['superadmin.laporan.show'])]
    public function test_report_pages_escape_user_content(string $routeName): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $report = Laporan::factory()->create(['judul' => '<script>alert("unsafe")</script>']);

        $response = $this->actingAs($superadmin)->get(route($routeName, $routeName === 'superadmin.laporan.show' ? $report : []));

        $response->assertOk()
            ->assertSee($report->judul)
            ->assertDontSee($report->judul, escape: false);
    }

    public function test_superadmin_can_open_read_only_report_details(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $report = Laporan::factory()->for(Instansi::factory(), 'instansi')->create();

        $response = $this->actingAs($superadmin)->get(route('superadmin.laporan.show', $report));

        $response->assertOk()
            ->assertSee($report->judul)
            ->assertSee($report->instansi->nama_instansi)
            ->assertSee('Belum ada perubahan status.')
            ->assertDontSee('name="status"', escape: false);
    }

    public function test_missing_report_returns_404(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superadmin)->get(route('superadmin.laporan.show', 999));

        $response->assertNotFound();
    }

    public function test_superadmin_cannot_change_report_status_through_admin_endpoints(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $report = Laporan::factory()->create(['status' => 'menunggu']);

        $response = $this->actingAs($superadmin)->patch(route('admin.laporan.status.update', $report->id), ['status' => 'diproses']);

        $response->assertForbidden();
        $this->assertDatabaseHas('laporan', ['id' => $report->id, 'status' => 'menunggu']);
        $this->assertDatabaseCount('riwayat_status_laporan', 0);
    }

    public function test_superadmin_cannot_assign_officers_through_admin_endpoints(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $report = Laporan::factory()->create(['status' => 'menunggu']);

        $response = $this->actingAs($superadmin)->post(route('admin.penugasan.store', $report->id), ['petugas_id' => $superadmin->id]);

        $response->assertForbidden();
        $this->assertDatabaseCount('penugasan', 0);
        $this->assertDatabaseHas('laporan', ['id' => $report->id, 'status' => 'menunggu']);
    }

    #[TestWith(['admin_instansi'])]
    #[TestWith(['developer'])]
    public function test_existing_admin_roles_keep_access_to_their_report_list(string $role): void
    {
        $instansi = Instansi::factory()->create();
        $user = User::factory()->for($instansi, 'instansi')->create(['role' => $role]);
        $report = Laporan::factory()->for($instansi, 'instansi')->create();

        $response = $this->actingAs($user)->get(route('admin.laporan.index'));

        $response->assertOk()->assertSee($report->judul);
    }
}
