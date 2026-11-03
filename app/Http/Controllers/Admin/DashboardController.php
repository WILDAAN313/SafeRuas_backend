<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Support\IndramayuBoundary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, IndramayuBoundary $boundary): View
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
            ->get(['id', 'judul', 'deskripsi', 'kategori', 'status', 'alamat_lokasi', 'created_at']);

        $ringkasanKategori = (clone $laporanQuery)
            ->selectRaw('kategori, COUNT(*) as jumlah')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->groupBy('kategori')
            ->orderByDesc('jumlah')
            ->get();

        $laporanPeta = (clone $laporanQuery)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->limit(300)
            ->get(['id', 'judul', 'kategori', 'status', 'latitude', 'longitude', 'alamat_lokasi'])
            ->map(fn (Laporan $laporan): array => [
                'id' => $laporan->id,
                'judul' => $laporan->judul,
                'kategori' => $laporan->kategori,
                'status' => $laporan->status,
                'latitude' => (float) $laporan->latitude,
                'longitude' => (float) $laporan->longitude,
                'alamat' => $laporan->alamat_lokasi,
                'url' => route('admin.laporan.show', $laporan),
            ]);
        $regionBounds = $boundary->bounds();

        return view('admin.dashboard', compact(
            'totalLaporan',
            'laporanMenunggu',
            'laporanDiproses',
            'laporanSelesai',
            'laporanTerbaru',
            'ringkasanKategori',
            'laporanPeta',
            'regionBounds',
        ));
    }
}
