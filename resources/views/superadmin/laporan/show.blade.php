@extends('superadmin.layouts.app')

@section('title', 'Detail Laporan')
@section('breadcrumb', 'Detail laporan')

@section('content')
    <section class="grid gap-5 text-slate-800">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Laporan #SR-{{ $laporan->id }}</p><h1 class="mt-1 text-2xl font-extrabold tracking-tight">{{ $laporan->judul }}</h1><p class="mt-2 text-sm text-slate-500">{{ $laporan->kategori }} <span class="px-1 text-slate-300">•</span> {{ $laporan->created_at?->format('d/m/Y H:i') }}</p></div>
            <a href="{{ route('superadmin.laporan.index') }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">← Kembali ke laporan</a>
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Bukti dan uraian laporan">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-bold">Foto bukti kerusakan</h2><span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-semibold text-blue-700">{{ ucfirst($laporan->status) }}</span></div>
                @if ($laporan->foto)
                    <img src="{{ asset('storage/' . $laporan->foto) }}" alt="Foto kerusakan {{ $laporan->judul }}" class="max-h-[520px] w-full bg-slate-100 object-contain" loading="lazy">
                @else
                    <div class="grid min-h-56 place-items-center bg-slate-50 p-6 text-sm text-slate-500">Foto bukti belum tersedia.</div>
                @endif
                <div class="grid gap-2 border-t border-slate-100 p-5"><h2 class="text-sm font-bold">Uraian kerusakan</h2><p class="whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $laporan->deskripsi ?: 'Pelapor tidak menambahkan uraian.' }}</p></div>
            </section>

            <div class="grid content-start gap-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="report-info-title"><h2 id="report-info-title" class="text-sm font-bold">Informasi laporan</h2><dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2 xl:grid-cols-1">
                    <div><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status laporan</dt><dd class="mt-1 font-semibold">{{ ucfirst($laporan->status) }}</dd></div>
                    <div><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kategori</dt><dd class="mt-1">{{ $laporan->kategori ?: 'Kategori belum tersedia' }}</dd></div>
                    <div><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Pelapor</dt><dd class="mt-1">{{ $laporan->pelapor?->name ?? 'Tidak tersedia' }}<span class="mt-1 block text-xs text-slate-500">{{ $laporan->pelapor?->email ?? '' }}</span></dd></div>
                    <div><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Instansi penanganan</dt><dd class="mt-1">{{ $laporan->instansi?->nama_instansi ?? 'Belum ditentukan' }}</dd></div>
                    <div><dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Waktu laporan</dt><dd class="mt-1">{{ $laporan->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
                </dl><p class="mt-5 rounded-xl bg-blue-50 p-3 text-xs leading-relaxed text-blue-800">Penanganan dan perubahan status dilakukan oleh Admin Instansi. Super Admin memantau progres laporan.</p></section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="location-title"><div class="flex items-center justify-between gap-2"><h2 id="location-title" class="text-sm font-bold">Lokasi laporan</h2>@if ($laporan->latitude !== null && $laporan->longitude !== null)<a href="https://www.openstreetmap.org/?mlat={{ $laporan->latitude }}&mlon={{ $laporan->longitude }}#map=17/{{ $laporan->latitude }}/{{ $laporan->longitude }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline">Buka OpenStreetMap ↗</a>@endif</div><p class="mt-3 text-sm text-slate-600">{{ $laporan->alamat_lokasi ?: 'Alamat belum tersedia' }}</p><p class="mt-2 break-all font-mono text-[11px] text-slate-500">{{ $laporan->latitude ?? '-' }}, {{ $laporan->longitude ?? '-' }}</p></section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="history-title"><h2 id="history-title" class="text-sm font-bold">Riwayat penanganan</h2><ol class="mt-4 grid gap-4">
                    @forelse ($laporan->riwayatStatus as $riwayat)
                        <li class="border-l-2 border-blue-200 pl-4"><p class="text-xs font-semibold">{{ ucfirst($riwayat->status_sebelumnya ?? 'Baru') }} → {{ ucfirst($riwayat->status_baru) }}</p><p class="mt-1 text-xs text-slate-500">{{ $riwayat->pengguna?->name ?? 'Pengguna tidak tersedia' }} · {{ $riwayat->created_at?->format('d/m/Y H:i') }}</p>@if ($riwayat->catatan)<p class="mt-2 whitespace-pre-line text-xs text-slate-600">{{ $riwayat->catatan }}</p>@endif</li>
                    @empty
                        <li class="text-xs text-slate-500">Belum ada perubahan status.</li>
                    @endforelse
                </ol></section>
            </div>
        </div>
    </section>
@endsection
