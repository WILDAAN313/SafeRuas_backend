<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MasyarakatController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
        ]);

        $query = User::query()->where('role', 'masyarakat')->withCount([
            'laporan' => fn (Builder $query): Builder => $query->withinIndramayu(),
        ]);

        if (isset($filters['cari']) && trim($filters['cari']) !== '') {
            $search = trim($filters['cari']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (($filters['status'] ?? null) === 'aktif') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'nonaktif') {
            $query->where('is_active', false);
        }

        $masyarakat = $query->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();
        $jumlahPengguna = User::query()
            ->where('role', 'masyarakat')
            ->selectRaw('COUNT(*) AS total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS aktif, SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS nonaktif')
            ->first();
        $ringkasan = (object) [
            'total' => (int) ($jumlahPengguna->total ?? 0),
            'aktif' => (int) ($jumlahPengguna->aktif ?? 0),
            'nonaktif' => (int) ($jumlahPengguna->nonaktif ?? 0),
        ];

        return view('superadmin.masyarakat.index', compact('masyarakat', 'ringkasan'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'masyarakat', 404);

        $laporan = Laporan::query()
            ->withinIndramayu()
            ->where('user_id', $user->id)
            ->with('instansi:id,nama_instansi')
            ->latest()
            ->orderByDesc('id')
            ->paginate(10, ['id', 'instansi_id', 'judul', 'kategori', 'status', 'created_at']);

        return view('superadmin.masyarakat.show', [
            'masyarakat' => $user,
            'laporan' => $laporan,
        ]);
    }
}
