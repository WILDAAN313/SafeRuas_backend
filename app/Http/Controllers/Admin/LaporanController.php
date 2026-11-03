<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\RiwayatStatusLaporan;
use App\Support\IndramayuBoundary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    private const KATEGORI_LAPORAN = ['Kerusakan Jalan', 'Kerusakan Rambu'];

    public function index(Request $request, IndramayuBoundary $boundary): View
    {
        $instansiId = $request->user()->instansi_id;
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:menunggu,diproses,selesai'],
            'kategori' => ['nullable', Rule::in(self::KATEGORI_LAPORAN)],
        ]);

        $query = DB::table('laporan')
            ->leftJoin('users', 'laporan.user_id', '=', 'users.id')
            ->where('laporan.instansi_id', $instansiId)
            ->select(
                'laporan.id',
                'laporan.judul',
                'laporan.deskripsi',
                'laporan.kategori',
                'laporan.alamat_lokasi',
                'laporan.status',
                'laporan.latitude',
                'laporan.longitude',
                'laporan.foto',
                'laporan.created_at',
                'users.name as nama_pelapor',
                'users.email as email_pelapor'
            );

        if (! empty($filters['cari'])) {
            $search = trim($filters['cari']);

            $query->where(function ($builder) use ($search): void {
                $builder->where('laporan.judul', 'like', "%{$search}%")
                    ->orWhere('laporan.kategori', 'like', "%{$search}%")
                    ->orWhere('laporan.alamat_lokasi', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $builder->orWhere('laporan.id', (int) $search);
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('laporan.status', $filters['status']);
        }

        if (! empty($filters['kategori'])) {
            $query->where('laporan.kategori', $filters['kategori']);
        }

        $laporan = $query
            ->orderByDesc('laporan.created_at')
            ->orderByDesc('laporan.id')
            ->paginate(10)
            ->withQueryString();

        $laporanPeta = $laporan->getCollection()
            ->map(fn (object $item): array => [
                'id' => $item->id,
                'judul' => $item->judul,
                'kategori' => $item->kategori,
                'status' => $item->status,
                'alamat' => $item->alamat_lokasi,
                'latitude' => is_numeric($item->latitude) ? (float) $item->latitude : null,
                'longitude' => is_numeric($item->longitude) ? (float) $item->longitude : null,
                'url' => route('admin.laporan.show', $item->id),
            ]);

        $jumlahStatus = DB::table('laporan')
            ->where('instansi_id', $instansiId)
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalLaporan = (int) $jumlahStatus->sum();
        $menunggu = (int) ($jumlahStatus['menunggu'] ?? 0);
        $diproses = (int) ($jumlahStatus['diproses'] ?? 0);
        $selesai = (int) ($jumlahStatus['selesai'] ?? 0);

        $regionBounds = $boundary->bounds();
        $kategoriList = self::KATEGORI_LAPORAN;

        return view('admin.laporan.index', compact(
            'laporan',
            'laporanPeta',
            'regionBounds',
            'totalLaporan',
            'menunggu',
            'diproses',
            'selesai',
            'kategoriList'
        ));
    }

    public function show(Request $request, int $id): View
    {
        $laporan = Laporan::query()
            ->with([
                'pelapor:id,name,email',
                'riwayatStatus' => fn ($query) => $query
                    ->with('pengguna:id,name')
                    ->latest(),
            ])
            ->where('instansi_id', $request->user()->instansi_id)
            ->findOrFail($id);

        return view('admin.laporan.detail', compact('laporan'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:diproses,selesai'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $request->user();
        $changed = DB::transaction(function () use ($admin, $id, $validated): bool {
            $laporan = Laporan::query()
                ->where('instansi_id', $admin->instansi_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($laporan->status === $validated['status']) {
                return false;
            }

            $nextStatuses = match ($laporan->status) {
                'menunggu' => ['diproses'],
                'diproses' => ['selesai'],
                default => [],
            };

            if (! in_array($validated['status'], $nextStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak sesuai alur penanganan laporan.',
                ]);
            }

            if ($validated['status'] === 'selesai' && ! $laporan->penugasan()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Tugaskan petugas sebelum menyelesaikan laporan.',
                ]);
            }

            RiwayatStatusLaporan::query()->create([
                'laporan_id' => $laporan->id,
                'user_id' => $admin->id,
                'status_sebelumnya' => $laporan->status,
                'status_baru' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $laporan->update(['status' => $validated['status']]);

            return true;
        });

        return back()->with(
            'success',
            $changed ? 'Status laporan berhasil diperbarui.' : 'Status laporan tidak berubah.'
        );
    }
}
