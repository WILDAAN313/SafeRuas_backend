@extends('superadmin.layouts.app')

@section('title', 'Dashboard Superadmin')
@section('breadcrumb', 'Dashboard dan peta')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div><p class="text-[10px] font-bold tracking-widest text-blue-600">PEMANTAUAN KABUPATEN INDRAMAYU</p><h1 class="mt-2 text-2xl font-extrabold tracking-tight">Dashboard Superadmin</h1><p class="mt-2 text-sm text-slate-500">Lihat sebaran laporan warga Kabupaten Indramayu dan pantau progres penanganannya.</p></div>
        <a href="{{ route('superadmin.laporan.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-700">Seluruh laporan &rarr;</a>
    </div>

    @php
        $jumlahStatus = $statistikStatus->pluck('jumlah', 'status');
        $ringkasan = [
            ['label' => 'Total laporan', 'jumlah' => $totalLaporan, 'warna' => 'bg-blue-500', 'keterangan' => 'Seluruh instansi'],
            ['label' => 'Menunggu', 'jumlah' => $jumlahStatus->get('menunggu', 0), 'warna' => 'bg-amber-500', 'keterangan' => 'Menunggu tindak lanjut'],
            ['label' => 'Diproses', 'jumlah' => $jumlahStatus->get('diproses', 0), 'warna' => 'bg-blue-600', 'keterangan' => 'Dalam penanganan'],
            ['label' => 'Selesai', 'jumlah' => $jumlahStatus->get('selesai', 0), 'warna' => 'bg-emerald-500', 'keterangan' => 'Penanganan selesai'],
        ];
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ($ringkasan as $item)
            <article class="rounded-2xl border border-[#e8edf5] bg-white p-4 sm:p-5">
                <p class="flex items-center gap-2 text-xs font-semibold text-slate-500"><span class="size-2 rounded-full {{ $item['warna'] }}" aria-hidden="true"></span>{{ $item['label'] }}</p>
                <p class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($item['jumlah']) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ $item['keterangan'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('superadmin.admin_instansi.index') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-[#e8edf5] bg-white p-5 hover:border-blue-300 focus-visible:outline-2 focus-visible:outline-blue-600">
            <div>
                <p class="text-xs font-semibold text-slate-500">Admin Instansi</p>
                <p class="mt-1 text-[11px] text-slate-400">Kelola akun dan akses admin</p>
            </div>
            <span class="text-3xl font-extrabold text-blue-700">{{ number_format($totalAdminInstansi) }}</span>
        </a>
        <a href="{{ route('superadmin.masyarakat.index') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-[#e8edf5] bg-white p-5 hover:border-blue-300 focus-visible:outline-2 focus-visible:outline-blue-600">
            <div>
                <p class="text-xs font-semibold text-slate-500">Masyarakat</p>
                <p class="mt-1 text-[11px] text-slate-400">Pantau pengguna dan riwayat laporan</p>
            </div>
            <span class="text-3xl font-extrabold text-blue-700">{{ number_format($totalMasyarakat) }}</span>
        </a>
    </div>

    <section id="report-map-panel" class="overflow-hidden rounded-2xl border border-[#e8edf5] bg-white" aria-labelledby="map-title">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5">
            <div><h2 id="map-title" class="font-bold">Peta sebaran laporan Indramayu</h2><p class="mt-1 text-xs text-slate-500">Peta berfokus pada Kabupaten Indramayu. Klik pin untuk melihat detail laporan.</p></div>
            <div class="flex items-center gap-2">
                <button id="map-reset" type="button" class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Kembali ke Indramayu</button>
                <button id="map-fullscreen" type="button" class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" hidden>Layar penuh</button>
            </div>
        </div>
        <form id="map-filters" class="flex flex-wrap items-end gap-3 border-b border-slate-100 bg-slate-50/60 p-4" aria-label="Filter peta laporan">
            <label class="grid min-w-[160px] flex-1 gap-1.5 text-[11px] font-semibold text-slate-500">Status laporan
                <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 focus:border-blue-500 focus:outline-blue-500"><option value="">Semua status</option><option value="menunggu">Menunggu</option><option value="diproses">Diproses</option><option value="selesai">Selesai</option></select>
            </label>
            <label class="grid min-w-[180px] flex-1 gap-1.5 text-[11px] font-semibold text-slate-500">Kategori kerusakan
                <select name="kategori" class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 focus:border-blue-500 focus:outline-blue-500"><option value="">Semua kategori</option>@foreach ($kategoriList as $kategori)<option value="{{ $kategori }}">{{ $kategori }}</option>@endforeach</select>
            </label>
            <div class="flex flex-wrap gap-3 py-2.5 text-[11px] text-slate-500" aria-label="Legenda status">
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-amber-500" aria-hidden="true"></span>Menunggu</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-blue-600" aria-hidden="true"></span>Diproses</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-500" aria-hidden="true"></span>Selesai</span>
            </div>
        </form>
        <div class="grid grid-cols-1 min-[1200px]:grid-cols-[minmax(0,1fr)_280px]">
            <div class="relative min-w-0">
                <div id="report-map" data-source="{{ route('superadmin.laporan.peta') }}" data-region-bounds="{{ json_encode($regionBounds) }}" class="relative z-0 h-[440px] bg-slate-100 sm:h-[560px]" role="region" aria-label="Peta lokasi laporan masyarakat Kabupaten Indramayu"></div>
                <div id="map-empty" class="pointer-events-none absolute inset-0 z-[500] grid place-items-center p-6" hidden><p class="rounded-xl border border-slate-200 bg-white/95 p-5 text-center text-sm text-slate-600 shadow-sm">Tidak ada laporan berkoordinat valid pada area dan filter ini.</p></div>
            </div>
            <aside class="flex max-h-[360px] flex-col border-t border-slate-100 min-[1200px]:max-h-[560px] min-[1200px]:border-t-0 min-[1200px]:border-l" aria-label="Laporan di area peta">
                <div class="border-b border-slate-100 p-4"><h3 class="text-xs font-bold">Laporan di area ini</h3><p id="map-list-summary" class="mt-1 text-[11px] text-slate-400">Memuat lokasi laporan...</p></div>
                <div id="map-report-list" class="min-h-24 flex-1 divide-y divide-slate-100 overflow-y-auto" aria-busy="true"></div>
            </aside>
        </div>
        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-4 py-3">
            <p class="w-full text-[11px] text-slate-500">Cakupan laporan mengikuti batas Kabupaten Indramayu dari <a href="https://geoservices.big.go.id/rbi/rest/services/Hosted/Wilayah_Administrasi_Kabupaten__Kota/FeatureServer/0" class="text-blue-700 hover:underline">BIG, September 2023</a>, termasuk wilayah kepulauan.</p>
            <p id="map-satellite-note" class="w-full text-[11px] leading-relaxed text-slate-500" hidden>Citra satelit Sentinel-2 cloudless 2025. Ketajaman citra terbatas saat diperbesar; gunakan Peta biasa untuk membaca nama jalan.</p>
            <p id="map-message" class="flex-1 text-xs text-slate-500" role="status" aria-live="polite">Memuat peta...</p>
            <button id="map-retry" type="button" class="cursor-pointer text-xs font-bold text-blue-700 hover:underline" hidden>Coba lagi</button>
        </div>
        <noscript><p class="border-t border-slate-100 p-4 text-sm text-slate-600">Aktifkan JavaScript untuk menggunakan peta. Anda tetap dapat membuka daftar dan detail laporan melalui menu Seluruh laporan.</p></noscript>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-[#e8edf5] bg-white" aria-labelledby="recent-title">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5"><h2 id="recent-title" class="font-bold">Laporan terbaru</h2><p class="text-xs text-slate-400">{{ number_format($totalAdminInstansi) }} admin instansi &middot; {{ number_format($totalMasyarakat) }} masyarakat</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs [&_th]:px-5 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-400 [&_td]:px-5 [&_td]:py-4">
                <thead class="bg-slate-50"><tr><th scope="col">Laporan</th><th scope="col">Pelapor</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($laporanTerbaru as $laporan)
                        <tr><td class="min-w-48 font-semibold">{{ $laporan->judul }}</td><td>{{ $laporan->pelapor?->name ?? 'Tidak tersedia' }}</td><td>{{ ucfirst($laporan->status) }}</td><td class="whitespace-nowrap">{{ $laporan->created_at?->format('d/m/Y') }}</td><td><a href="{{ route('superadmin.laporan.show', $laporan) }}" class="font-semibold text-blue-700 hover:underline">Detail</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400">Belum ada laporan masyarakat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
    @vite('resources/js/superadmin-map.js')
@endpush
