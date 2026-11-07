<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Support\IndramayuBoundary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LaporanController extends Controller
{
    private const KATEGORI = ['jalan_rusak', 'fasilitas'];

    private const DUPLICATE_RADIUS_METER = 20;
    private const DUPLICATE_WINDOW_MENIT = 10;

    public function index(Request $request): JsonResponse
    {
        $paginator = Laporan::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $data = collect($paginator->items())
            ->map(fn (Laporan $laporan) => $this->transform($request, $laporan))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan berhasil diambil',
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul'         => ['required', 'string', 'max:150'],
            'deskripsi'     => ['required', 'string', 'max:2000'],
            'kategori'      => ['required', 'in:'.implode(',', self::KATEGORI)],
            'latitude'      => ['required', 'numeric', 'between:-90,90'],
            'longitude'     => ['required', 'numeric', 'between:-180,180'],
            'alamat_lokasi' => ['nullable', 'string', 'max:255'],
            'foto'          => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], $this->messages(), $this->attributes());

        $user = $request->user();
        $lat  = (float) $validated['latitude'];
        $lng  = (float) $validated['longitude'];

        if (! app(IndramayuBoundary::class)->contains($lat, $lng)) {
            $pesan = 'Lokasi Anda berada di luar wilayah Kabupaten Indramayu. Laporan hanya dapat dikirim dari dalam Indramayu.';

            return response()->json([
                'success' => false,
                'message' => $pesan,
                'errors'  => ['latitude' => [$pesan]],
            ], 422);
        }

        if ($this->isDuplicate($user->id, $validated['kategori'], $lat, $lng)) {
            $pesan = 'Anda sudah mengirim laporan serupa di lokasi ini beberapa menit lalu. Mohon tunggu sebelum mengirim lagi.';

            return response()->json([
                'success' => false,
                'message' => $pesan,
                'errors'  => ['laporan' => [$pesan]],
            ], 422);
        }

        $path = $request->file('foto')->store('laporan', 'public');

        if (! $path) {
            return response()->json([
                'success' => false,
                'message' => 'Foto gagal disimpan. Silakan coba lagi.',
            ], 500);
        }

        try {
            $laporan = Laporan::create([
                'user_id'       => $user->id,
                'judul'         => trim($validated['judul']),
                'deskripsi'     => trim($validated['deskripsi']),
                'kategori'      => $validated['kategori'],
                'foto'          => $path,
                'latitude'      => $lat,
                'longitude'     => $lng,
                'alamat_lokasi' => $validated['alamat_lokasi'] ?? null,
                'status'        => 'menunggu',
            ]);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Laporan gagal disimpan. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim',
            'data'    => $this->transform($request, $laporan),
        ], 201);
    }

    private function isDuplicate(int $userId, string $kategori, float $lat, float $lng): bool
    {
        $kandidat = Laporan::where('user_id', $userId)
            ->where('kategori', $kategori)
            ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MENIT))
            ->get(['latitude', 'longitude']);

        foreach ($kandidat as $item) {
            $jarak = $this->jarakMeter($lat, $lng, (float) $item->latitude, (float) $item->longitude);

            if ($jarak < self::DUPLICATE_RADIUS_METER) {
                return true;
            }
        }

        return false;
    }

    private function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    private function transform(Request $request, Laporan $laporan): array
    {
        return [
            'id'       => $laporan->id,
            'judul'    => $laporan->judul,
            'kategori' => $laporan->kategori,
            'status'   => $laporan->status,
            'foto'     => $this->fotoUrl($request, $laporan->foto),
            'alamat'   => $laporan->alamat_lokasi,
            'tanggal'  => $laporan->created_at?->format('d M Y'),
        ];
    }

    private function fotoUrl(Request $request, ?string $foto): ?string
    {
        if (! $foto) {
            return null;
        }

        return $request->getSchemeAndHttpHost().'/storage/'.ltrim($foto, '/');
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => [
                'string' => ':attribute maksimal :max karakter.',
                'file'   => 'Ukuran :attribute maksimal 5 MB.',
            ],
            'in'       => ':attribute tidak valid.',
            'numeric'  => ':attribute harus berupa angka.',
            'between'  => ':attribute di luar rentang yang valid.',
            'image'    => ':attribute harus berupa gambar.',
            'mimes'    => ':attribute harus berformat JPG, JPEG, atau PNG.',
            'file'     => ':attribute harus berupa berkas.',
            'uploaded' => 'Unggah :attribute gagal. Pastikan ukuran maksimal 5 MB.',
        ];
    }

    private function attributes(): array
    {
        return [
            'judul'         => 'Judul',
            'deskripsi'     => 'Deskripsi',
            'kategori'      => 'Kategori',
            'latitude'      => 'Latitude',
            'longitude'     => 'Longitude',
            'alamat_lokasi' => 'Alamat lokasi',
            'foto'          => 'Foto',
        ];
    }
}