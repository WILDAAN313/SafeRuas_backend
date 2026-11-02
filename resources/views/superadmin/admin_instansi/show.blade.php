@extends('superadmin.layouts.app')

@section('title', 'Detail Admin Instansi')
@section('breadcrumb', 'Detail Admin Instansi')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4"><div><h1 class="text-2xl font-extrabold">{{ $adminInstansi->name }}</h1><p class="mt-2 text-sm text-slate-500">Detail akun Admin Instansi.</p></div><a href="{{ route('superadmin.admin_instansi.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600">Kembali ke daftar</a></div>
    <section class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="account-title">
        <h2 id="account-title" class="font-bold">Informasi akun</h2>
        <dl class="mt-5 grid gap-5 text-sm sm:grid-cols-2">
            <div><dt class="text-xs text-slate-400">Nama lengkap</dt><dd class="mt-1 font-semibold">{{ $adminInstansi->name }}</dd></div>
            <div><dt class="text-xs text-slate-400">Email</dt><dd class="mt-1 break-words">{{ $adminInstansi->email }}</dd></div>
            <div><dt class="text-xs text-slate-400">Instansi</dt><dd class="mt-1">{{ $adminInstansi->instansi?->nama_instansi ?? 'Belum ditentukan' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Status akun</dt><dd class="mt-1 font-semibold {{ $adminInstansi->is_active ? 'text-emerald-700' : 'text-red-700' }}">{{ $adminInstansi->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Terdaftar sejak</dt><dd class="mt-1">{{ $adminInstansi->created_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Aktivitas penanganan</dt><dd class="mt-1">{{ $adminInstansi->riwayat_status_count }} perubahan status &middot; {{ $adminInstansi->penugasan_dibuat_count }} penugasan</dd></div>
        </dl>
        <p class="mt-6 rounded-lg bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">Akun yang memiliki riwayat terkait tidak dapat dihapus. Gunakan status Nonaktif untuk menghentikan akses tanpa menghilangkan riwayat.</p>
        <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
            <a href="{{ route('superadmin.admin_instansi.edit', $adminInstansi) }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Edit akun</a>
            <form method="POST" action="{{ route('superadmin.admin_instansi.destroy', $adminInstansi) }}" onsubmit="return confirm('Hapus akun ini? Akun yang memiliki riwayat terkait tidak dapat dihapus.');">@csrf @method('DELETE')<button type="submit" class="cursor-pointer text-xs font-semibold text-red-600 hover:underline">Hapus akun</button></form>
        </div>
    </section>
@endsection
