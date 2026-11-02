@extends('superadmin.layouts.app')

@section('title', 'Detail Laporan')
@section('breadcrumb', 'Detail laporan')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4"><div><p class="text-xs font-semibold text-blue-600">LAPORAN #{{ $laporan->id }}</p><h1 class="mt-2 text-2xl font-extrabold">{{ $laporan->judul }}</h1><p class="mt-2 text-sm text-slate-500">{{ $laporan->kategori }} &middot; {{ $laporan->created_at?->format('d/m/Y H:i') }}</p></div><a href="{{ route('superadmin.dashboard') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-blue-700">Kembali ke peta</a></div>
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" aria-label="Informasi laporan">
            @if ($laporan->foto)
                <img src="{{ asset('storage/'.$laporan->foto) }}" alt="Foto kerusakan {{ $laporan->judul }}" class="max-h-[440px] w-full bg-slate-100 object-contain" loading="lazy">
            @else
                <div class="grid min-h-44 place-items-center bg-slate-100 p-6 text-sm text-slate-400">Foto kerusakan belum tersedia.</div>
            @endif
            <div class="grid gap-5 p-6"><div><h2 class="text-sm font-bold">Deskripsi kerusakan</h2><p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-500">{{ $laporan->deskripsi }}</p></div><div><h2 class="text-sm font-bold">Lokasi</h2><p class="mt-2 text-sm text-slate-500">{{ $laporan->alamat_lokasi ?? 'Alamat belum tersedia' }}</p><p class="mt-2 text-xs text-slate-400">Latitude: {{ $laporan->latitude ?? '-' }} &middot; Longitude: {{ $laporan->longitude ?? '-' }}</p></div></div>
        </section>
        <div class="grid content-start gap-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="handling-title"><h2 id="handling-title" class="text-sm font-bold">Informasi penanganan</h2><dl class="mt-5 grid gap-4 text-sm"><div><dt class="text-xs text-slate-400">Status laporan</dt><dd class="mt-1 font-semibold">{{ ucfirst($laporan->status) }}</dd></div><div><dt class="text-xs text-slate-400">Instansi</dt><dd class="mt-1">{{ $laporan->instansi?->nama_instansi ?? 'Belum ditentukan' }}</dd></div><div><dt class="text-xs text-slate-400">Pelapor</dt><dd class="mt-1">{{ $laporan->pelapor?->name ?? 'Tidak tersedia' }}</dd></div></dl><p class="mt-5 rounded-lg bg-blue-50 p-3 text-xs leading-relaxed text-blue-700">Penanganan dan perubahan status dilakukan oleh Admin Instansi. Superadmin memantau progres laporan.</p></section>
            <section class="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="history-title"><h2 id="history-title" class="text-sm font-bold">Riwayat penanganan</h2><ol class="mt-5 grid gap-5">
                @forelse ($laporan->riwayatStatus as $riwayat)
                    <li class="border-l-2 border-blue-100 pl-4"><p class="text-xs font-semibold">{{ ucfirst($riwayat->status_sebelumnya ?? 'Baru') }} &rarr; {{ ucfirst($riwayat->status_baru) }}</p><p class="mt-1 text-xs text-slate-500">{{ $riwayat->pengguna?->name ?? 'Pengguna tidak tersedia' }}</p>@if ($riwayat->catatan)<p class="mt-2 whitespace-pre-line text-xs text-slate-500">{{ $riwayat->catatan }}</p>@endif<p class="mt-1 text-[11px] text-slate-400">{{ $riwayat->created_at?->format('d/m/Y H:i') }}</p></li>
                @empty
                    <li class="text-xs text-slate-400">Belum ada perubahan status.</li>
                @endforelse
            </ol></section>
        </div>
    </div>
@endsection
