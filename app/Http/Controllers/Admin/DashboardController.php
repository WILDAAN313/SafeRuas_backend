<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['admin_instansi', 'developer'], true), 403, 'Akses ditolak.');

        $laporanQuery = Laporan::query();

        if ($user->role === 'admin_instansi') {
            $laporanQuery->where('instansi_id', $user->instansi_id);
        }

        $jumlahStatus = (clone $laporanQuery)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalLaporan = (int) $jumlahStatus->sum();
        $laporanMenunggu = (int) ($jumlahStatus['menunggu'] ?? 0);
        $laporanDiproses = (int) ($jumlahStatus['diproses'] ?? 0);
        $laporanSelesai = (int) ($jumlahStatus['selesai'] ?? 0);

        $laporanTerbaru = (clone $laporanQuery)
            ->latest()
            ->take(5)
            ->get(['id', 'judul', 'deskripsi', 'kategori', 'status', 'created_at']);

        return view('admin.dashboard', compact(
            'totalLaporan',
            'laporanMenunggu',
            'laporanDiproses',
            'laporanSelesai',
            'laporanTerbaru'
        ));
    }
}
