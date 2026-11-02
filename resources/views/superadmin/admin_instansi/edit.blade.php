@extends('superadmin.layouts.app')

@section('title', 'Edit Admin Instansi')
@section('breadcrumb', 'Edit Admin Instansi')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4"><div><h1 class="text-2xl font-extrabold">Edit Admin Instansi</h1><p class="mt-2 text-sm text-slate-500">Perbarui identitas, instansi, dan akses akun admin.</p></div><a href="{{ route('superadmin.admin_instansi.show', $adminInstansi) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600">Kembali ke detail</a></div>
    <form method="POST" action="{{ route('superadmin.admin_instansi.update', $adminInstansi) }}" class="grid max-w-3xl gap-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        @csrf @method('PUT')
        <label class="grid gap-2 text-xs font-semibold text-slate-600">Nama lengkap<input name="name" value="{{ old('name', $adminInstansi->name) }}" required maxlength="255" autocomplete="name" class="rounded-lg border border-slate-200 px-3 py-3 text-sm"></label>
        <label class="grid gap-2 text-xs font-semibold text-slate-600">Email<input type="email" name="email" value="{{ old('email', $adminInstansi->email) }}" required maxlength="255" autocomplete="email" class="rounded-lg border border-slate-200 px-3 py-3 text-sm"></label>
        <label class="grid gap-2 text-xs font-semibold text-slate-600">Instansi<select name="instansi_id" required class="rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm"><option value="">Pilih instansi</option>@foreach ($instansiList as $instansi)<option value="{{ $instansi->id }}" @selected((string) old('instansi_id', $adminInstansi->instansi_id) === (string) $instansi->id)>{{ $instansi->nama_instansi }}</option>@endforeach</select></label>
        <label class="grid gap-2 text-xs font-semibold text-slate-600">Status akun<select name="is_active" required class="rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm"><option value="1" @selected((string) old('is_active', (int) $adminInstansi->is_active) === '1')>Aktif</option><option value="0" @selected((string) old('is_active', (int) $adminInstansi->is_active) === '0')>Nonaktif</option></select><span class="text-[11px] font-normal text-slate-400">Akun nonaktif tidak dapat login atau melanjutkan sesi sebelumnya.</span></label>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-xs font-semibold text-slate-600">Password baru<input type="password" name="password" minlength="8" autocomplete="new-password" class="rounded-lg border border-slate-200 px-3 py-3 text-sm"><span class="text-[11px] font-normal text-slate-400">Kosongkan jika password tidak diubah.</span></label>
            <label class="grid content-start gap-2 text-xs font-semibold text-slate-600">Konfirmasi password baru<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" class="rounded-lg border border-slate-200 px-3 py-3 text-sm"></label>
        </div>
        <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5"><button type="submit" @disabled($instansiList->isEmpty()) class="cursor-pointer rounded-lg bg-blue-600 px-5 py-3 text-xs font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Simpan perubahan</button><a href="{{ route('superadmin.admin_instansi.show', $adminInstansi) }}" class="px-3 py-3 text-xs font-semibold text-slate-500">Batal</a></div>
    </form>
@endsection
