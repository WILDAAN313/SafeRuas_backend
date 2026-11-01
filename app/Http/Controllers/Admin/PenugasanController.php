<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\RiwayatStatusLaporan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenugasanController extends Controller
{
    public function index(Request $request): View
    {
        $instansiId = $request->user()->instansi_id;

        $laporan = Laporan::query()
            ->with(['penugasan.petugas'])
            ->where('instansi_id', $instansiId)
            ->whereIn('status', ['menunggu', 'diproses'])
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(10);

        $petugas = User::query()
            ->where('role', 'petugas')
            ->where('instansi_id', $instansiId)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.penugasan.index', compact('laporan', 'petugas'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validate([
            'petugas_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->where('role', 'petugas')
                    ->where('instansi_id', $admin->instansi_id)),
            ],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($admin, $id, $validated): void {
            $laporan = Laporan::query()
                ->where('instansi_id', $admin->instansi_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($laporan->status === 'selesai') {
                throw ValidationException::withMessages([
                    'petugas_id' => 'Laporan yang sudah selesai tidak dapat ditugaskan kembali.',
                ]);
            }

            $laporan->penugasan()->create([
                'petugas_id' => $validated['petugas_id'],
                'admin_id' => $admin->id,
                'catatan' => $validated['catatan'] ?? null,
                'tanggal_penugasan' => now(),
            ]);

            if ($laporan->status === 'menunggu') {
                RiwayatStatusLaporan::query()->create([
                    'laporan_id' => $laporan->id,
                    'user_id' => $admin->id,
                    'status_sebelumnya' => 'menunggu',
                    'status_baru' => 'diproses',
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                $laporan->update(['status' => 'diproses']);
            }
        });

        return back()->with('success', 'Petugas berhasil ditugaskan ke laporan.');
    }
}
