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
        $statistikStatus = (clone $laporanQuery)
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $totalLaporan = (int) $statistikStatus->sum('jumlah');
        $laporanTerbaru = (clone $laporanQuery)
            ->with('pelapor:id,name')
            ->latest()
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'user_id', 'judul', 'status', 'created_at']);
        $kategoriList = (clone $laporanQuery)
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
        $regionBounds = $boundary->bounds();

        return view('superadmin.dashboard.index', compact(
            'totalAdminInstansi',
            'totalMasyarakat',
            'totalLaporan',
            'statistikStatus',
            'laporanTerbaru',
            'kategoriList',
            'regionBounds',
        ));
    }
}
