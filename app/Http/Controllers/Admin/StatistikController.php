<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    private const KATEGORI_LAPORAN = ['Kerusakan Jalan', 'Kerusakan Rambu'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
        ]);

        $laporanQuery = Laporan::query()
            ->where('instansi_id', $request->user()->instansi_id);

        if (! empty($filters['periode'])) {
            $year = (int) substr($filters['periode'], 0, 4);
            $month = (int) substr($filters['periode'], 5, 2);

            $laporanQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        $jumlahStatus = (clone $laporanQuery)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $jumlahStatusKategori = (clone $laporanQuery)
            ->whereIn('kategori', self::KATEGORI_LAPORAN)
            ->selectRaw('kategori, status, COUNT(*) as jumlah')
            ->groupBy('kategori', 'status')
            ->get()
            ->groupBy('kategori');

        $kategoriStatistik = collect(self::KATEGORI_LAPORAN)
            ->map(function (string $kategori) use ($jumlahStatusKategori): object {
                $jumlahStatus = $jumlahStatusKategori->get($kategori, collect())->pluck('jumlah', 'status');
                $total = (int) $jumlahStatus->sum();
                $jumlahMenungguKategori = (int) ($jumlahStatus['menunggu'] ?? 0);
                $jumlahDiprosesKategori = (int) ($jumlahStatus['diproses'] ?? 0);
                $jumlahSelesaiKategori = (int) ($jumlahStatus['selesai'] ?? 0);

                return (object) [
                    'kategori' => $kategori,
                    'total' => $total,
                    'menunggu' => $jumlahMenungguKategori,
                    'diproses' => $jumlahDiprosesKategori,
                    'selesai' => $jumlahSelesaiKategori,
                    'menungguPersen' => $total > 0 ? round($jumlahMenungguKategori / $total * 100, 1) : 0,
                    'diprosesPersen' => $total > 0 ? round($jumlahDiprosesKategori / $total * 100, 1) : 0,
                    'selesaiPersen' => $total > 0 ? round($jumlahSelesaiKategori / $total * 100, 1) : 0,
                ];
            });

        $totalLaporan = (int) $jumlahStatus->sum();
        $jumlahMenunggu = (int) ($jumlahStatus['menunggu'] ?? 0);
        $jumlahDiproses = (int) ($jumlahStatus['diproses'] ?? 0);
        $jumlahSelesai = (int) ($jumlahStatus['selesai'] ?? 0);
        $persentaseSelesai = $totalLaporan > 0
            ? round($jumlahSelesai / $totalLaporan * 100, 1)
            : 0.0;

        return view('admin.statistik.index', compact(
            'filters',
            'totalLaporan',
            'jumlahMenunggu',
            'jumlahDiproses',
            'jumlahSelesai',
            'kategoriStatistik',
            'persentaseSelesai'
        ));
    }
}
