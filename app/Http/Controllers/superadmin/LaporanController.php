<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:menunggu,diproses,selesai'],
            'kategori' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Laporan::query()->withinIndramayu()->with('pelapor:id,name,email');

        if (! empty($filters['cari'])) {
            $search = trim($filters['cari']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('judul', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhereHas('pelapor', function ($pelaporQuery) use ($search): void {
                        $pelaporQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        $laporan = $query->latest()->orderByDesc('id')
            ->paginate(15, ['id', 'user_id', 'judul', 'kategori', 'alamat_lokasi', 'status', 'created_at'])
            ->withQueryString();
        $kategoriList = Laporan::query()
            ->withinIndramayu()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('superadmin.laporan.index', compact('laporan', 'kategoriList'));
    }

    public function show(Laporan $laporan): View
    {
        abort_unless($laporan->isWithinIndramayu(), 404);

        $laporan->load([
            'pelapor:id,name,email',
            'instansi:id,nama_instansi',
            'riwayatStatus' => fn ($query) => $query
                ->with('pengguna:id,name')
                ->latest(),
        ]);

        return view('superadmin.laporan.show', compact('laporan'));
    }
}
