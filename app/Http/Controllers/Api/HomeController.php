<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // SESUAIKAN dengan nilai status yang dipakai dashboard admin.
    private const STATUS_MENUNGGU = ['menunggu'];

    // Laporan yang sudah lolos verifikasi (termasuk yang sudah ditangani)
    private const STATUS_TERVERIFIKASI = ['terverifikasi', 'diproses', 'diperbaiki', 'selesai'];

    private const STATUS_SELESAI = ['diperbaiki', 'selesai'];

    // Laporan yang boleh tampil di "Kondisi Saat Ini": sudah diverifikasi, belum selesai
    private const STATUS_TAMPIL_SEKITAR = ['terverifikasi', 'diproses'];

    private const NEARBY_RADIUS_KM = 2;
    private const NEARBY_LIMIT = 10;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $lat = $request->query('lat');
        $lon = $request->query('lon');

        // STATISTIK LAPORAN PENGGUNA (satu query)
        $counts = Laporan::where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $sum = fn (array $statuses) => (int) $counts->only($statuses)->sum();

        // LAPORAN TERKINI MILIK PENGGUNA (semua status, supaya user lihat progresnya)
        $recentReports = Laporan::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Laporan $laporan) => [
                'id'       => $laporan->id,
                'judul'    => $laporan->judul,
                'alamat'   => $laporan->alamat_lokasi,
                'kategori' => $laporan->kategori,
                'status'   => $laporan->status,
                'foto'     => $this->fotoUrl($request, $laporan->foto),
                'tanggal'  => $laporan->created_at?->format('d M Y'),
            ])
            ->values();

        // KONDISI SEKITAR (semua pengguna, hanya yang sudah diverifikasi)
        $nearby = collect();

        if (is_numeric($lat) && is_numeric($lon)) {
            $lat = (float) $lat;
            $lon = (float) $lon;

            if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                $nearby = Laporan::query()
                    ->withinIndramayu()
                    ->whereIn('status', self::STATUS_TAMPIL_SEKITAR)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->select([
                        'id',
                        'judul',
                        'alamat_lokasi',
                        'kategori',
                        'status',
                        'foto',
                    ])
                    ->selectRaw(
                        '(6371 * ACOS(
                            LEAST(1, GREATEST(-1,
                                COS(RADIANS(?)) *
                                COS(RADIANS(latitude)) *
                                COS(RADIANS(longitude) - RADIANS(?)) +
                                SIN(RADIANS(?)) *
                                SIN(RADIANS(latitude))
                            ))
                        )) AS jarak_km',
                        [$lat, $lon, $lat]
                    )
                    ->having('jarak_km', '<=', self::NEARBY_RADIUS_KM)
                    ->orderBy('jarak_km')
                    ->limit(self::NEARBY_LIMIT)
                    ->get()
                    ->map(fn (Laporan $laporan) => [
                        'id'       => $laporan->id,
                        'judul'    => $laporan->judul,
                        'alamat'   => $laporan->alamat_lokasi,
                        'kategori' => $laporan->kategori,
                        'status'   => $laporan->status,
                        'foto'     => $this->fotoUrl($request, $laporan->foto),
                        'jarak_km' => round((float) $laporan->jarak_km, 2),
                    ])
                    ->values();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Home berhasil diambil',

            'stats' => [
                'total'         => (int) $counts->sum(),
                'terverifikasi' => $sum(self::STATUS_TERVERIFIKASI),
                'diperbaiki'    => $sum(self::STATUS_SELESAI),
                'menunggu'      => $sum(self::STATUS_MENUNGGU),
            ],

            'recent_reports' => $recentReports,
            'nearby'         => $nearby,
        ]);
    }

    private function fotoUrl(Request $request, ?string $foto): ?string
    {
        if (! $foto) {
            return null;
        }

        return $request->getSchemeAndHttpHost().'/storage/'.ltrim($foto, '/');
    }
}