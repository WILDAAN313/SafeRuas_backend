<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\superadmin\SaveAdminInstansiRequest;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        if (isset($filters['cari']) && trim($filters['cari']) !== '') {
            $search = trim($filters['cari']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $adminInstansi = $query->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();

        return view('superadmin.admin_instansi.index', compact('adminInstansi'));
    }

    public function create(): View
    {
        $instansiList = Instansi::query()->orderBy('nama_instansi')->orderBy('id')->get(['id', 'nama_instansi']);

        return view('superadmin.admin_instansi.create', compact('instansiList'));
    }

    public function store(SaveAdminInstansiRequest $request): RedirectResponse
    {
        $admin = new User(['role' => 'admin_instansi']);
        $this->saveAccount($admin, $request->validated());

        return redirect()->route('superadmin.admin_instansi.show', $admin)
            ->with('success', 'Akun Admin Instansi berhasil dibuat.');
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'admin_instansi', 404);

        $user->load('instansi:id,nama_instansi');
        $user->loadCount(['riwayatStatus', 'penugasanDibuat']);

        return view('superadmin.admin_instansi.show', ['adminInstansi' => $user]);
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === 'admin_instansi', 404);

        $instansiList = Instansi::query()->orderBy('nama_instansi')->orderBy('id')->get(['id', 'nama_instansi']);

        return view('superadmin.admin_instansi.edit', ['adminInstansi' => $user, 'instansiList' => $instansiList]);
    }

    public function update(SaveAdminInstansiRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'admin_instansi', 404);

        $this->saveAccount($user, $request->validated());

        return redirect()->route('superadmin.admin_instansi.show', $user)
            ->with('success', 'Data Admin Instansi berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === 'admin_instansi', 404);

        DB::transaction(function () use ($user): void {
            $admin = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($admin->role === 'admin_instansi', 404);

            if ($admin->riwayatStatus()->exists() || $admin->penugasanDibuat()->exists()
                || $admin->penugasanPetugas()->exists() || $admin->laporan()->exists()) {
                throw ValidationException::withMessages([
                    'akun' => 'Akun memiliki riwayat terkait dan tidak dapat dihapus. Nonaktifkan akun untuk menjaga riwayat.',
                ]);
            }

            $admin->delete();
        });

        return redirect()->route('superadmin.admin_instansi.index')
            ->with('success', 'Akun Admin Instansi berhasil dihapus.');
    }

    /**
     * @param  array{name: string, email: string, instansi_id: int|string, is_active: bool|int|string, password?: ?string}  $data
     */
    private function saveAccount(User $user, array $data): void
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'instansi_id' => (int) $data['instansi_id'],
            'is_active' => (bool) $data['is_active'],
        ]);

        if (isset($data['password']) && $data['password'] !== '') {
            $user->password = $data['password'];
        }

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'Email tersebut sudah digunakan.']);
        }
    }
}
