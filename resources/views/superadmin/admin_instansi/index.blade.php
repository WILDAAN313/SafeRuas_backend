@extends('superadmin.layouts.app')

@section('title', 'Admin Instansi')
@section('breadcrumb', 'Admin Instansi')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div><h1 class="text-2xl font-extrabold">Admin Instansi</h1><p class="mt-2 text-sm text-slate-500">Kelola akun yang bertanggung jawab menangani laporan masyarakat.</p></div>
        <a href="{{ route('superadmin.admin_instansi.create') }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Tambah Admin Instansi</a>
    </div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" aria-label="Daftar Admin Instansi">
        <form action="{{ route('superadmin.admin_instansi.index') }}" method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-5">
            <label class="grid min-w-48 flex-1 gap-2 text-xs font-semibold text-slate-500">Cari admin<input type="search" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Nama atau email admin" class="rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700"></label>
            <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Cari</button>
            @if (request()->filled('cari'))<a href="{{ route('superadmin.admin_instansi.index') }}" class="px-3 py-2.5 text-xs font-semibold text-slate-500 hover:text-blue-700">Reset</a>@endif
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs [&_th]:px-5 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-400 [&_td]:px-5 [&_td]:py-4">
                <thead class="bg-slate-50"><tr><th scope="col">Admin</th><th scope="col">Instansi</th><th scope="col">Status akun</th><th scope="col">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($adminInstansi as $admin)
                        <tr>
                            <td class="min-w-48"><p class="font-semibold">{{ $admin->name }}</p><p class="mt-1 text-slate-400">{{ $admin->email }}</p></td>
                            <td>{{ $admin->instansi?->nama_instansi ?? 'Belum ditentukan' }}</td>
                            <td><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $admin->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="flex items-center gap-3 whitespace-nowrap">
                                <a href="{{ route('superadmin.admin_instansi.show', $admin) }}" class="font-semibold text-blue-700 hover:underline">Detail</a>
                                <a href="{{ route('superadmin.admin_instansi.edit', $admin) }}" class="font-semibold text-slate-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('superadmin.admin_instansi.destroy', $admin) }}" onsubmit="return confirm('Hapus akun ini? Akun yang memiliki riwayat terkait tidak dapat dihapus.');">@csrf @method('DELETE')<button type="submit" class="cursor-pointer font-semibold text-red-600 hover:underline" aria-label="Hapus akun {{ $admin->name }}">Hapus</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-400">Belum ada Admin Instansi yang sesuai dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-5">{{ $adminInstansi->links() }}</div>
    </section>
@endsection
