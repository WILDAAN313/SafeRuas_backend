<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminInstansiController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        $query = User::query()
            ->with('instansi:id,nama_instansi')
            ->where('role', 'admin_instansi');

        if (! empty($filters['cari'])) {
            $search = trim($filters['cari']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $adminInstansi = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('superadmin.admin_instansi.index', compact('adminInstansi'));
    }

    public function create(): View
    {
        $instansi = Instansi::query()->orderBy('id')->firstOrFail();

        return view('superadmin.admin_instansi.create', compact('instansi'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $instansi = Instansi::query()->orderBy('id')->firstOrFail();

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin_instansi',
            'instansi_id' => $instansi->id,
        ]);

        return back()->with('success', 'Akun admin instansi berhasil dibuat.');
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'admin_instansi', 404);

        $user->load('instansi:id,nama_instansi');

        return view('superadmin.admin_instansi.show', ['adminInstansi' => $user]);
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === 'admin_instansi', 404);

        return view('superadmin.admin_instansi.edit', ['adminInstansi' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'admin_instansi', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Data admin instansi berhasil diperbarui.');
    }
}
