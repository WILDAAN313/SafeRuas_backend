@extends('admin.layouts.app')

@section('title', 'Dashboard Admin Instansi')
@section('breadcrumb', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@section('content')
    <section class="grid gap-6 text-[#172b4d]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold tracking-[1.5px] text-blue-700">STATUS SISTEM SIAGA <span class="px-1 text-slate-400">•</span> DATA REAL-TIME</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight max-[600px]:text-2xl">Monitoring Infrastruktur Jalan</h1>
                <p class="mt-2 text-sm text-slate-600">Ringkasan laporan infrastruktur dan progres penanganan pada instansi Anda.</p>
            </div>
            <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-xs font-bold text-white hover:bg-blue-800">Buka semua laporan <span aria-hidden="true">→</span></a>
        </div>

        <div class="grid grid-cols-4 gap-4 max-[1000px]:grid-cols-2 max-[560px]:grid-cols-1">
            @foreach ([['Total Laporan', $totalLaporan, 'Semua laporan instansi'], ['Menunggu Verifikasi', $laporanMenunggu, 'Perlu ditindaklanjuti'], ['Sedang Diproses', $laporanDiproses, 'Dalam penanganan'], ['Laporan Selesai', $laporanSelesai, 'Penanganan tuntas']] as [$label, $jumlah, $keterangan])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                    <p class="mt-5 text-4xl font-extrabold tracking-tight text-[#10233c]">{{ number_format($jumlah, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $keterangan }}</p>
                </article>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="map-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div><h2 id="map-title" class="text-xl font-bold">Peta Sebaran Laporan Infrastruktur</h2><p class="mt-1 text-sm text-slate-500">Klik marker untuk melihat informasi laporan dan membuka detail.</p></div>
                <div class="flex flex-wrap gap-3 text-xs text-slate-600" aria-label="Legenda status peta">
                    <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-red-500"></i>Menunggu</span>
                    <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-blue-600"></i>Diproses</span>
                    <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-emerald-500"></i>Selesai</span>
                </div>
            </div>
            <div id="admin-report-map" data-reports="{{ json_encode($laporanPeta) }}" data-region-bounds="{{ json_encode($regionBounds) }}" class="h-[420px] bg-slate-100 sm:h-[500px]" role="region" aria-label="Peta lokasi laporan instansi"></div>
            <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">{{ number_format($laporanPeta->count(), 0, ',', '.') }} lokasi laporan ditampilkan · OpenStreetMap</p>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="recent-title">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 p-5">
                <div><h2 id="recent-title" class="text-xl font-bold">Laporan Masuk Terbaru</h2><p class="mt-1 text-sm text-slate-500">Daftar laporan kerusakan yang membutuhkan tindak lanjut instansi.</p></div>
                <a href="{{ route('admin.laporan.index') }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">Buka Semua Laporan →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-xs [&_th]:bg-slate-50 [&_th]:px-5 [&_th]:py-3 [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-5 [&_td]:py-4">
                    <thead><tr><th scope="col">ID Laporan</th><th scope="col">Waktu Masuk</th><th scope="col">Kategori</th><th scope="col">Lokasi &amp; Laporan</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($laporanTerbaru as $laporan)
                            @php
                                $statusClass = match ($laporan->status) {
                                    'menunggu' => 'bg-red-50 text-red-700',
                                    'diproses' => 'bg-blue-50 text-blue-700',
                                    'selesai' => 'bg-emerald-50 text-emerald-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr>
                                <td class="font-bold text-blue-700">#REP-{{ $laporan->id }}</td>
                                <td class="whitespace-nowrap text-slate-600">{{ $laporan->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                <td><span class="rounded-md bg-blue-50 px-2 py-1 font-semibold text-blue-800">{{ ucfirst($laporan->kategori ?? 'Lainnya') }}</span></td>
                                <td><div class="max-w-[320px] font-semibold text-slate-800">{{ $laporan->judul }}</div><div class="mt-1 max-w-[320px] truncate text-slate-500">{{ $laporan->alamat_lokasi ?? 'Lokasi belum tersedia' }}</div></td>
                                <td><span class="rounded-full px-2.5 py-1 font-semibold {{ $statusClass }}">{{ ucfirst($laporan->status ?? 'Tidak diketahui') }}</span></td>
                                <td><a href="{{ route('admin.laporan.show', $laporan->id) }}" class="font-bold text-blue-700 hover:underline">Detail →</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-12 text-center text-slate-500">Belum ada laporan untuk instansi Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="category-title">
            <div class="mb-4"><h2 id="category-title" class="text-lg font-bold">Ringkasan Kategori Kerusakan</h2><p class="mt-1 text-sm text-slate-500">Jumlah laporan berdasarkan kategori pada instansi Anda.</p></div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @forelse ($ringkasanKategori as $kategori)
                    <div class="rounded-xl bg-slate-50 p-4"><p class="truncate text-xs font-semibold text-slate-600">{{ ucfirst($kategori->kategori) }}</p><p class="mt-2 text-2xl font-extrabold text-blue-700">{{ number_format($kategori->jumlah, 0, ',', '.') }}</p></div>
                @empty
                    <p class="col-span-full py-4 text-sm text-slate-500">Belum ada kategori laporan.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
    @vite('resources/js/admin-dashboard-map.js')
@endpush
