<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPengguna = User::query()->count();
        $totalAdminInstansi = User::query()->where('role', 'admin_instansi')->count();
        $totalMasyarakat = User::query()->where('role', 'masyarakat')->count();
        $totalLaporan = Laporan::query()->count();
        $statistikStatus = Laporan::query()
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $laporanTerbaru = Laporan::query()
            ->with('pelapor:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return view('superadmin.dashboard.index', compact(
            'totalPengguna',
            'totalAdminInstansi',
            'totalMasyarakat',
            'totalLaporan',
            'statistikStatus',
            'laporanTerbaru',
        ));
    }
}
