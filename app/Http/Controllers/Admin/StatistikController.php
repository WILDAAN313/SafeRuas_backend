<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
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

        $kategoriStatistik = (clone $laporanQuery)
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->selectRaw('kategori, COUNT(*) as jumlah')
            ->groupBy('kategori')
            ->orderByDesc('jumlah')
            ->orderBy('kategori')
            ->get();

        $totalLaporan = (int) $jumlahStatus->sum();
        $jumlahMenunggu = (int) ($jumlahStatus['menunggu'] ?? 0);
        $jumlahDiproses = (int) ($jumlahStatus['diproses'] ?? 0);
        $jumlahSelesai = (int) ($jumlahStatus['selesai'] ?? 0);
        $maksimumKategori = max(1, (int) $kategoriStatistik->max('jumlah'));

        return view('admin.statistik.index', compact(
            'filters',
            'totalLaporan',
            'jumlahMenunggu',
            'jumlahDiproses',
            'jumlahSelesai',
            'kategoriStatistik',
            'maksimumKategori'
        ));
    }
}
