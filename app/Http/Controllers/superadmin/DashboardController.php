<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\User;
use App\Support\IndramayuBoundary;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(IndramayuBoundary $boundary): View
    {
        $jumlahPengguna = User::query()
            ->whereIn('role', ['admin_instansi', 'masyarakat'])
            ->select('role')
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('role')
            ->pluck('jumlah', 'role');
        $totalAdminInstansi = (int) $jumlahPengguna->get('admin_instansi', 0);
        $totalMasyarakat = (int) $jumlahPengguna->get('masyarakat', 0);
        $laporanQuery = Laporan::query()->withinIndramayu();
        $statistikKategoriStatus = (clone $laporanQuery)
            ->selectRaw('kategori, status, COUNT(*) as jumlah')
            ->groupBy('kategori', 'status')
            ->get();
        $statistikStatus = $statistikKategoriStatus
            ->groupBy('status')
            ->map(fn ($baris, string $status): object => (object) [
                'status' => $status,
                'jumlah' => (int) $baris->sum('jumlah'),
            ])
            ->sortBy('status')
            ->values();
        $kategoriStatistik = $statistikKategoriStatus
            ->groupBy('kategori')
            ->map(function ($baris, string $kategori): object {
                $jumlahPerStatus = $baris->pluck('jumlah', 'status');

                return (object) [
                    'kategori' => $kategori,
                    'total' => (int) $baris->sum('jumlah'),
                    'menunggu' => (int) ($jumlahPerStatus['menunggu'] ?? 0),
                    'diproses' => (int) ($jumlahPerStatus['diproses'] ?? 0),
                    'selesai' => (int) ($jumlahPerStatus['selesai'] ?? 0),
                ];
            })
            ->sortByDesc('total')
            ->values();
        $totalLaporan = (int) $statistikStatus->sum('jumlah');
        $laporanTerbaru = (clone $laporanQuery)
            ->with('pelapor:id,name')
            ->latest()
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'user_id', 'judul', 'status', 'created_at']);
        $kategoriList = $statistikKategoriStatus->pluck('kategori')->filter()->unique()->sort()->values();
        $trendMulai = now()->startOfMonth()->subMonths(5);
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $laporanPerBulan = (clone $laporanQuery)
            ->whereBetween('created_at', [$trendMulai, now()])
            ->selectRaw("{$monthExpression} AS bulan, COUNT(*) AS jumlah")
            ->selectRaw("SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS jumlah_selesai")
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');
        $trenLaporan = collect(range(0, 5))->map(function (int $offset) use ($trendMulai, $laporanPerBulan): object {
            $bulan = $trendMulai->copy()->addMonths($offset);
            $statistikBulan = $laporanPerBulan->get($bulan->format('Y-m'));

            return (object) [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => (int) ($statistikBulan->jumlah ?? 0),
                'selesai' => (int) ($statistikBulan->jumlah_selesai ?? 0),
            ];
        });
        $maksimumTren = max(1, (int) $trenLaporan->max('total'));
        $regionBounds = $boundary->bounds();

        return view('superadmin.dashboard.index', compact(
            'totalAdminInstansi',
            'totalMasyarakat',
            'totalLaporan',
            'statistikStatus',
            'kategoriStatistik',
            'trenLaporan',
            'maksimumTren',
            'laporanTerbaru',
            'kategoriList',
            'regionBounds',
        ));
    }
}
