<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Identitas pengguna berdasarkan token Sanctum
        $userId = $request->user()->id;

        // Koordinat GPS dari Flutter
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        
        // STATISTIK LAPORAN PENGGUNA

        $userReports = Laporan::where('user_id', $userId);

        $total = (clone $userReports)->count();

        // Sesuaikan nilai status dengan database kamu
        $menunggu = (clone $userReports)
            ->where('status', 'menunggu')
            ->count();

        $terverifikasi = (clone $userReports)
            ->where('status', 'terverifikasi')
            ->count();

        $diperbaiki = (clone $userReports)
            ->whereIn('status', ['diperbaiki', 'selesai'])
            ->count();

        // LAPORAN TERKINI MILIK PENGGUNA
        $recentReports = (clone $userReports)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($laporan) use ($request) {

                return [
                    'id' => $laporan->id,
                    'judul' => $laporan->judul,
                    'alamat' => $laporan->alamat_lokasi,
                    'kategori' => $laporan->kategori,
                    'status' => $laporan->status,

                    // Foto disimpan di Laravel Storage
                    'foto' => $laporan->foto
                        ? $request->getSchemeAndHttpHost()
                            . '/storage/' . ltrim($laporan->foto, '/')
                        : null,

                    'tanggal' => $laporan->created_at
                        ? $laporan->created_at->format('d M Y')
                        : null,
                ];
            });

        // KONDISI SEKITAR DARI SEMUA PENGGU
        $nearby = collect();

        if (is_numeric($lat) && is_numeric($lon)) {

            $lat = (float) $lat;
            $lon = (float) $lon;

            if (
                $lat >= -90 && $lat <= 90 &&
                $lon >= -180 && $lon <= 180
            ) {

                $nearby = Laporan::query()
                    ->withinIndramayu()
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

                    ->having('jarak_km', '<=', 2)
                    ->orderBy('jarak_km')
                    ->limit(10)
                    ->get()

                    ->map(function ($laporan) use ($request) {

                        return [
                            'id' => $laporan->id,
                            'judul' => $laporan->judul,
                            'alamat' => $laporan->alamat_lokasi,
                            'kategori' => $laporan->kategori,
                            'status' => $laporan->status,

                            'foto' => $laporan->foto
                                ? $request->getSchemeAndHttpHost()
                                    . '/storage/' . ltrim($laporan->foto, '/')
                                : null,

                            'jarak_km' => round(
                                (float) $laporan->jarak_km,
                                2
                            ),
                        ];
                    });
            }
        }

        // KIRIM DATA KE FLUTTER
        return response()->json([
            'success' => true,
            'message' => 'Data Home berhasil diambil',

            'stats' => [
                'total' => $total,
                'terverifikasi' => $terverifikasi,
                'diperbaiki' => $diperbaiki,
                'menunggu' => $menunggu,
            ],

            'recent_reports' => $recentReports,

            'nearby' => $nearby,
        ]);
    }
}