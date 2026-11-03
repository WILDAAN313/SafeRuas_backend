@extends('superadmin.layouts.app')

@section('title', 'Dashboard Super Admin')
@section('breadcrumb', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@section('content')
    @php
        $jumlahStatus = $statistikStatus->pluck('jumlah', 'status');
        $kartuStatistik = [
            ['label' => 'Total laporan', 'jumlah' => $totalLaporan, 'warna' => 'bg-blue-50 text-blue-700'],
            ['label' => 'Menunggu', 'jumlah' => $jumlahStatus->get('menunggu', 0), 'warna' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Diproses', 'jumlah' => $jumlahStatus->get('diproses', 0), 'warna' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Selesai', 'jumlah' => $jumlahStatus->get('selesai', 0), 'warna' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Admin Instansi', 'jumlah' => $totalAdminInstansi, 'warna' => 'bg-indigo-50 text-indigo-700'],
            ['label' => 'Masyarakat', 'jumlah' => $totalMasyarakat, 'warna' => 'bg-violet-50 text-violet-700'],
        ];
    @endphp

    <section class="grid gap-5 text-slate-800">
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div>
                <p class="text-[10px] font-bold tracking-[1.5px] text-blue-700">PUSAT KENDALI SUPER ADMIN</p>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight">Pemantauan SafeRuas Indramayu</h1>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">Pantau laporan, pengguna, dan progres penanganan dari data yang tercatat di SafeRuas.</p>
            </div>
            <a href="{{ route('superadmin.laporan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-xs font-bold text-white hover:bg-blue-700">Buka seluruh laporan →</a>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 2xl:grid-cols-6">
            @foreach ($kartuStatistik as $item)
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-2"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p><span class="grid size-8 place-items-center rounded-lg {{ $item['warna'] }}" aria-hidden="true">●</span></div>
                    <p class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($item['jumlah'], 0, ',', '.') }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="trend-title">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Analisis kronologis</p><h2 id="trend-title" class="mt-1 text-lg font-bold">Tren laporan enam bulan terakhir</h2><p class="mt-1 text-xs text-slate-500">Laporan dikelompokkan menurut bulan masuk; status selesai menunjukkan status saat ini.</p></div>
                    <div class="flex gap-3 text-[10px] text-slate-500"><span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-blue-600"></i>Laporan masuk</span><span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-emerald-500"></i>Saat ini selesai</span></div>
                </div>
                <div class="mt-5 grid h-48 grid-cols-6 items-end gap-2 border-b border-slate-100 px-1 sm:gap-4" role="img" aria-label="Grafik jumlah laporan masuk dan jumlah laporan berstatus selesai berdasarkan bulan masuk">
                    @foreach ($trenLaporan as $bulan)
                        <div class="flex h-full min-w-0 flex-col items-center justify-end gap-2">
                            <div class="flex h-full w-full items-end justify-center gap-1">
                                <span class="w-3 rounded-t bg-blue-600 sm:w-5" style="height: {{ $bulan->total / $maksimumTren * 100 }}%" title="{{ $bulan->total }} laporan masuk"></span>
                                <span class="w-3 rounded-t bg-emerald-500 sm:w-5" style="height: {{ $bulan->selesai / $maksimumTren * 100 }}%" title="{{ $bulan->selesai }} berstatus selesai"></span>
                            </div>
                            <span class="truncate text-[9px] text-slate-500 sm:text-[10px]">{{ $bulan->label }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="category-title">
                <div class="flex items-start justify-between gap-3"><div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Distribusi jenis masalah</p><h2 id="category-title" class="mt-1 text-lg font-bold">Kategori kerusakan</h2><p class="mt-1 text-xs text-slate-500">Jumlah laporan dan status terkini per kategori database.</p></div><a href="{{ route('superadmin.laporan.index') }}" class="shrink-0 text-[10px] font-semibold text-blue-700 hover:underline">Lihat laporan</a></div>
                <div class="mt-4 grid gap-3">
                    @forelse ($kategoriStatistik as $kategori)
                        <article class="rounded-xl bg-[#f2f5ff] p-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2"><h3 class="text-sm font-bold">{{ $kategori->kategori ?: 'Kategori belum tersedia' }}</h3><strong class="text-lg text-blue-700">{{ number_format($kategori->total, 0, ',', '.') }}</strong></div>
                            <div class="mt-3 flex h-2 overflow-hidden rounded-full bg-white" aria-hidden="true">
                                @if ($kategori->total > 0)
                                    <span class="bg-emerald-500" style="width: {{ $kategori->selesai / $kategori->total * 100 }}%"></span><span class="bg-sky-500" style="width: {{ $kategori->diproses / $kategori->total * 100 }}%"></span><span class="bg-amber-500" style="width: {{ $kategori->menunggu / $kategori->total * 100 }}%"></span>
                                @endif
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-slate-600"><span class="text-emerald-700">{{ number_format($kategori->selesai) }} selesai</span><span class="text-sky-700">{{ number_format($kategori->diproses) }} diproses</span><span class="text-amber-700">{{ number_format($kategori->menunggu) }} menunggu</span></div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-4 text-xs text-slate-500">Belum ada laporan untuk diringkas.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section id="report-map-panel" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="map-title">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5">
                <div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Pemantauan wilayah</p><h2 id="map-title" class="mt-1 text-lg font-bold">Sebaran laporan Indramayu</h2><p class="mt-1 text-xs text-slate-500">Klik pin untuk membuka informasi laporan dari database.</p></div>
                <div class="flex flex-wrap items-center gap-2">
                    <button id="map-reset" type="button" class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Fokus Indramayu</button>
                    <button id="map-fullscreen" type="button" class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" hidden>Layar penuh</button>
                </div>
            </div>
            <form id="map-filters" class="flex flex-wrap items-end gap-3 border-b border-slate-100 bg-slate-50/70 p-4" aria-label="Filter peta laporan">
                <label class="grid min-w-40 flex-1 gap-1.5 text-[11px] font-semibold text-slate-500">Status laporan
                    <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 focus:border-blue-500 focus:outline-blue-500"><option value="">Semua status</option><option value="menunggu">Menunggu</option><option value="diproses">Diproses</option><option value="selesai">Selesai</option></select>
                </label>
                <label class="grid min-w-44 flex-1 gap-1.5 text-[11px] font-semibold text-slate-500">Kategori
                    <select name="kategori" class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 focus:border-blue-500 focus:outline-blue-500"><option value="">Semua kategori</option>@foreach ($kategoriList as $kategori)<option value="{{ $kategori }}">{{ $kategori }}</option>@endforeach</select>
                </label>
                <div class="flex flex-wrap gap-3 py-2 text-[10px] text-slate-500" aria-label="Legenda status"><span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-amber-500"></i>Menunggu</span><span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-blue-600"></i>Diproses</span><span class="inline-flex items-center gap-1.5"><i class="size-2 rounded-full bg-emerald-500"></i>Selesai</span></div>
            </form>
            <div class="grid grid-cols-1 min-[1200px]:grid-cols-[minmax(0,1fr)_280px]">
                <div class="relative min-w-0"><div id="report-map" data-source="{{ route('superadmin.laporan.peta') }}" data-region-bounds="{{ json_encode($regionBounds) }}" class="relative z-0 h-[380px] bg-slate-100 sm:h-[500px]" role="region" aria-label="Peta sebaran lokasi laporan Indramayu"></div><div id="map-empty" class="pointer-events-none absolute inset-0 z-[500] grid place-items-center p-6" hidden><p class="rounded-xl border border-slate-200 bg-white/95 p-5 text-center text-sm text-slate-600 shadow-sm">Tidak ada laporan berkoordinat pada area dan filter ini.</p></div></div>
                <aside class="flex max-h-[320px] flex-col border-t border-slate-100 min-[1200px]:max-h-[500px] min-[1200px]:border-t-0 min-[1200px]:border-l" aria-label="Laporan di area peta"><div class="border-b border-slate-100 p-4"><h3 class="text-xs font-bold">Laporan di area ini</h3><p id="map-list-summary" class="mt-1 text-[11px] text-slate-400">Memuat laporan...</p></div><div id="map-report-list" class="min-h-24 flex-1 divide-y divide-slate-100 overflow-y-auto" aria-busy="true"></div></aside>
            </div>
            <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-4 py-3"><p class="w-full text-[10px] text-slate-500">Peta sebaran laporan menggunakan batas cakupan Kabupaten Indramayu yang sudah dipakai aplikasi.</p><p id="map-message" class="flex-1 text-xs text-slate-500" role="status" aria-live="polite">Memuat peta...</p><button id="map-retry" type="button" class="cursor-pointer text-xs font-bold text-blue-700 hover:underline" hidden>Coba lagi</button></div>
            <noscript><p class="border-t border-slate-100 p-4 text-sm text-slate-600">Aktifkan JavaScript untuk menggunakan peta. Daftar dan detail laporan tersedia melalui menu Seluruh Laporan.</p></noscript>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="recent-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5"><div><h2 id="recent-title" class="font-bold">Laporan terbaru</h2><p class="mt-1 text-xs text-slate-500">Data laporan terbaru pada wilayah yang dipantau.</p></div><a href="{{ route('superadmin.laporan.index') }}" class="text-xs font-semibold text-blue-700 hover:underline">Seluruh laporan →</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[650px] text-left text-xs [&_th]:bg-slate-50 [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5"><thead><tr><th scope="col">Laporan</th><th scope="col">Pelapor</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead><tbody>
                @forelse ($laporanTerbaru as $laporan)
                    @php($statusClass = match ($laporan->status) { 'menunggu' => 'bg-amber-50 text-amber-700', 'diproses' => 'bg-blue-50 text-blue-700', 'selesai' => 'bg-emerald-50 text-emerald-700', default => 'bg-slate-100 text-slate-600' })
                    <tr><td><p class="font-semibold text-slate-800">{{ $laporan->judul }}</p><p class="mt-1 text-[10px] text-slate-500">{{ $laporan->kategori }}</p></td><td>{{ $laporan->pelapor?->name ?? 'Tidak tersedia' }}</td><td><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">{{ ucfirst($laporan->status) }}</span></td><td class="whitespace-nowrap text-slate-500">{{ $laporan->created_at?->format('d/m/Y') }}</td><td><a href="{{ route('superadmin.laporan.show', $laporan) }}" class="font-semibold text-blue-700 hover:underline">Detail →</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Belum ada laporan masyarakat.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
    @vite('resources/js/superadmin-map.js')
@endpush
