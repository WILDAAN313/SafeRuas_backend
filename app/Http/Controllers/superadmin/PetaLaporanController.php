<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetaLaporanController extends Controller
{
    private const int MAX_MARKERS = 500;

    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:menunggu,diproses,selesai'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'south' => ['required_with:west,north,east', 'numeric', 'between:-90,90'],
            'north' => ['required_with:south,west,east', 'numeric', 'between:-90,90', 'gte:south'],
            'west' => ['required_with:south,north,east', 'numeric', 'between:-180,180'],
            'east' => ['required_with:south,west,north', 'numeric', 'between:-180,180', 'gte:west'],
        ]);

        $query = Laporan::query()
            ->withinIndramayu();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['south'], $filters['north'], $filters['west'], $filters['east'])) {
            $query->whereBetween('latitude', [$filters['south'], $filters['north']])
                ->whereBetween('longitude', [$filters['west'], $filters['east']]);
        }

        $summary = (clone $query)
            ->selectRaw('COUNT(*) AS total, MIN(latitude) AS south, MAX(latitude) AS north, MIN(longitude) AS west, MAX(longitude) AS east')
            ->first();
        $total = (int) $summary->total;
        $laporan = $query
            ->with('instansi:id,nama_instansi')
            ->latest()
            ->orderByDesc('id')
            ->limit(self::MAX_MARKERS)
            ->get(['id', 'instansi_id', 'judul', 'kategori', 'status', 'alamat_lokasi', 'latitude', 'longitude', 'created_at'])
            ->map(fn (Laporan $laporan): array => [
                'id' => $laporan->id,
                'judul' => $laporan->judul,
                'kategori' => $laporan->kategori,
                'status' => $laporan->status,
                'alamat' => $laporan->alamat_lokasi,
                'latitude' => (float) $laporan->latitude,
                'longitude' => (float) $laporan->longitude,
                'instansi' => $laporan->instansi?->nama_instansi ?? 'Belum ditentukan',
                'tanggal' => $laporan->created_at?->format('d/m/Y'),
                'url' => route('superadmin.laporan.show', $laporan),
            ]);

        return response()->json([
            'data' => $laporan,
            'meta' => [
                'total' => $total,
                'ditampilkan' => $laporan->count(),
                'dibatasi' => $total > self::MAX_MARKERS,
            ],
            'bounds' => $total === 0 ? null : [
                [(float) $summary->south, (float) $summary->west],
                [(float) $summary->north, (float) $summary->east],
            ],
        ])->header('Cache-Control', 'private, no-store');
    }
}
