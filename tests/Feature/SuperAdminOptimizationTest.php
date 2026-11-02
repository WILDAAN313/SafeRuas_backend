<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use App\Support\IndramayuBoundary;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JsonException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminOptimizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    #[TestWith([1])]
    #[TestWith([12])]
    public function test_dashboard_query_count_stays_bounded_as_reporters_grow(int $reporters): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        User::factory()->create(['role' => 'admin_instansi']);
        User::factory()->count($reporters)->has(Laporan::factory(), 'laporan')->create(['role' => 'masyarakat']);

        $queries = 0;
        DB::listen(function (QueryExecuted $event) use (&$queries): void {
            $queries++;
        });

        $this->actingAs($superadmin)->get(route('superadmin.dashboard'))->assertOk()
            ->assertViewHas('totalAdminInstansi', 1)->assertViewHas('totalMasyarakat', $reporters)
            ->assertViewHas('totalLaporan', $reporters);

        $this->assertLessThanOrEqual(5, $queries);
    }

    #[TestWith(['coordinates', 'laporan'])]
    #[TestWith(['owner', 'laporan'])]
    #[TestWith(['users', 'users'])]
    public function test_monitoring_queries_use_indexes_instead_of_full_table_scans(string $case, string $table): void
    {
        $report = Laporan::factory()->create();
        $query = match ($case) {
            'coordinates' => Laporan::query()->withinIndramayu()->select('id'),
            'owner' => Laporan::query()->withinIndramayu()->where('user_id', $report->user_id)->select('id'),
            'users' => User::query()->where('role', 'masyarakat')->orderBy('name')->orderBy('id')->limit(15)->select('id', 'name'),
        };

        $plan = implode(' ', array_map(
            fn (object $row): string => $row->detail,
            DB::select('EXPLAIN QUERY PLAN '.$query->toSql(), $query->getBindings()),
        ));

        $this->assertStringContainsString('SEARCH '.$table.' USING', $plan);
        $this->assertStringNotContainsString('USE TEMP B-TREE FOR ORDER BY', $plan);
    }

    public function test_database_cache_restores_geometry_without_recompiling_or_retaining_connections(): void
    {
        config(['cache.default' => 'database']);
        Laporan::factory()->create();
        $boundary = app(IndramayuBoundary::class);
        $this->assertSame(1, Laporan::query()->withinIndramayu()->count());

        $this->app->forgetInstance(IndramayuBoundary::class);
        $queries = 0;
        DB::listen(function (QueryExecuted $event) use (&$queries): void {
            $queries++;
        });
        $restored = app(IndramayuBoundary::class);

        $this->assertNotSame($boundary, $restored);
        $this->assertTrue($restored->contains(-6.3276, 108.3249));
        $this->assertSame(1, Laporan::query()->withinIndramayu()->count());
        $this->assertLessThanOrEqual(2, $queries);
    }

    public function test_report_status_stays_fresh_while_geometry_is_cached(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $report = Laporan::factory()->create(['status' => 'menunggu']);

        $this->actingAs($superadmin)->getJson(route('superadmin.laporan.peta'))
            ->assertOk()->assertJsonPath('data.0.status', 'menunggu');

        $report->update(['status' => 'selesai']);

        $this->getJson(route('superadmin.laporan.peta'))
            ->assertOk()->assertJsonPath('data.0.status', 'selesai');
    }

    public function test_changing_the_boundary_source_invalidates_its_compiled_cache(): void
    {
        Storage::fake('local');
        $this->writeBoundary(108, -6);
        $path = Storage::disk('local')->path('indramayu.geojson');
        $first = IndramayuBoundary::fromGeoJsonFile($path);
        $this->assertTrue($first->contains(-5.95, 108.05));

        $this->writeBoundary(107, -7);
        $updated = IndramayuBoundary::fromGeoJsonFile($path);

        $this->assertFalse($updated->contains(-5.95, 108.05));
        $this->assertTrue($updated->contains(-6.95, 107.05));
    }

    public function test_a_corrupt_source_cannot_fall_back_to_an_old_cached_boundary(): void
    {
        Storage::fake('local');
        $this->writeBoundary(108, -6);
        $path = Storage::disk('local')->path('indramayu.geojson');
        IndramayuBoundary::fromGeoJsonFile($path);

        Storage::disk('local')->put('indramayu.geojson', '{invalid json');
        $this->expectException(JsonException::class);

        IndramayuBoundary::fromGeoJsonFile($path);
    }

    private function writeBoundary(float $west, float $south): void
    {
        Storage::disk('local')->put('indramayu.geojson', json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$west, $south], [$west + 0.1, $south], [$west + 0.1, $south + 0.1],
                    [$west, $south + 0.1], [$west, $south],
                ]],
            ],
        ], JSON_THROW_ON_ERROR));
    }
}
