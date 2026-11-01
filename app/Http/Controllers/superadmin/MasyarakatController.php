<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MasyarakatController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $query = User::query()->where('role', 'masyarakat');

        if (! empty($filters['cari'])) {
            $search = trim($filters['cari']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $masyarakat = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('superadmin.masyarakat.index', compact('masyarakat'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'masyarakat', 404);

        $laporan = Laporan::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('superadmin.masyarakat.show', [
            'masyarakat' => $user,
            'laporan' => $laporan,
        ]);
    }
}
