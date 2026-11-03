@extends('admin.layouts.app')

@section('title', 'Manajemen Laporan')
@section('breadcrumb', 'Manajemen Laporan')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@section('content')
    @php
        $activeReport = $laporan->first();
        $statusTabs = [
            '' => ['Semua', $totalLaporan],
            'menunggu' => ['Menunggu Verifikasi', $menunggu],
            'diproses' => ['Sedang Diproses', $diproses],
            'selesai' => ['Selesai', $selesai],
        ];
    @endphp

    <section class="grid gap-5 text-[#172b4d]">
        <div>
            <p class="text-[10px] font-bold tracking-[1.5px] text-blue-700">ADMIN INSTANSI <span class="px-1 text-slate-400">•</span> PENGELOLAAN LAPORAN</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight">Manajemen Laporan</h1>
            <p class="mt-1 text-sm text-slate-500">Verifikasi laporan masyarakat dan pantau tindak lanjut instansi.</p>
        </div>

        <div class="grid grid-cols-4 gap-3 max-[1000px]:grid-cols-2 max-[560px]:grid-cols-1">
            @foreach ([['Total Laporan', $totalLaporan, 'bg-blue-50 text-blue-700'], ['Menunggu Verifikasi', $menunggu, 'bg-red-50 text-red-700'], ['Sedang Dikerjakan', $diproses, 'bg-indigo-50 text-indigo-700'], ['Laporan Selesai', $selesai, 'bg-emerald-50 text-emerald-700']] as [$label, $jumlah, $accent])
                <article class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div><p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($jumlah, 0, ',', '.') }}</p></div>
                    <span class="grid size-10 place-items-center rounded-xl {{ $accent }}" aria-hidden="true">●</span>
                </article>
            @endforeach
        </div>

        <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-laporan-search>
            <div class="flex flex-wrap gap-2.5">
                <label class="relative min-w-[220px] flex-1">
                    <span class="sr-only">Cari laporan</span>
                    <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari ID, judul, pelapor, atau kategori..." class="min-h-11 w-full rounded-xl border-0 bg-[#eef3ff] px-4 text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-500">
                </label>
                <button type="submit" class="min-h-11 rounded-xl bg-blue-700 px-5 text-xs font-bold text-white hover:bg-blue-800">Cari laporan</button>
                <a href="{{ route('admin.laporan.index') }}" class="inline-flex min-h-11 items-center rounded-xl bg-[#eef3ff] px-4 text-xs font-semibold text-slate-700 hover:bg-blue-100">Reset filter</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <label class="grid gap-1 text-[10px] font-semibold text-slate-500">Status tiket
                    <select name="status" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu verifikasi</option>
                        <option value="diproses" @selected(request('status') === 'diproses')>Sedang diproses</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    </select>
                </label>
                <label class="grid gap-1 text-[10px] font-semibold text-slate-500">Kategori kerusakan
                    <select name="kategori" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua kerusakan</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-end"><button type="submit" class="min-h-10 w-full rounded-xl bg-[#eef3ff] px-4 text-xs font-semibold text-blue-800 hover:bg-blue-100">Terapkan filter</button></div>
            </div>
        </form>

        <nav class="flex flex-wrap gap-2" aria-label="Filter cepat status laporan">
            @foreach ($statusTabs as $status => [$label, $jumlah])
                <a href="{{ route('admin.laporan.index', array_filter(['cari' => request('cari'), 'kategori' => request('kategori'), 'status' => $status ?: null])) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold {{ request('status', '') === $status ? 'bg-blue-700 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-blue-50' }}">
                    {{ $label }} <span class="rounded-full px-2 py-0.5 text-[10px] {{ request('status', '') === $status ? 'bg-white/20' : 'bg-blue-50 text-blue-700' }}">{{ number_format($jumlah, 0, ',', '.') }}</span>
                </a>
            @endforeach
        </nav>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(340px,0.9fr)]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="report-list-title">
                <div class="border-b border-slate-100 px-4 py-4"><h2 id="report-list-title" class="font-bold">Daftar laporan</h2><p class="mt-1 text-xs text-slate-500">Pilih laporan untuk melihat foto, informasi pelapor, dan lokasi.</p></div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-xs [&_th]:bg-slate-50 [&_th]:px-3 [&_th]:py-3 [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-3 [&_td]:py-3">
                        <thead><tr><th scope="col">Laporan &amp; foto</th><th scope="col">Kategori &amp; lokasi</th><th scope="col">Status</th><th scope="col">Waktu</th><th scope="col">Detail</th></tr></thead>
                        <tbody>
                            @forelse ($laporan as $item)
                                @php
                                    $statusClass = match ($item->status) {
                                        'menunggu' => 'bg-red-100 text-red-700',
                                        'diproses' => 'bg-blue-100 text-blue-700',
                                        'selesai' => 'bg-emerald-100 text-emerald-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <tr
                                    data-report-row
                                    data-report-id="{{ $item->id }}"
                                    data-title="{{ $item->judul }}"
                                    data-description="{{ $item->deskripsi }}"
                                    data-category="{{ $item->kategori ?: 'Kategori belum tersedia' }}"
                                    data-address="{{ $item->alamat_lokasi ?: 'Alamat belum tersedia' }}"
                                    data-reporter="{{ $item->nama_pelapor ?? 'Pengguna tidak ditemukan' }}"
                                    data-email="{{ $item->email_pelapor ?? '-' }}"
                                    data-created-at="{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') : '-' }}"
                                    data-status="{{ $item->status }}"
                                    data-latitude="{{ $item->latitude }}"
                                    data-longitude="{{ $item->longitude }}"
                                    data-photo="{{ $item->foto ? asset('storage/' . $item->foto) : '' }}"
                                    data-detail-url="{{ route('admin.laporan.show', $item->id) }}"
                                    data-status-url="{{ route('admin.laporan.status.update', $item->id) }}"
                                    class="cursor-pointer hover:bg-blue-50/70 {{ $loop->first ? 'bg-blue-50' : '' }}"
                                    tabindex="0"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                >
                                    <td><div class="flex min-w-48 items-center gap-2.5">
                                        @if ($item->foto)
                                            <img src="{{ asset('storage/' . $item->foto) }}" alt="" class="size-11 shrink-0 rounded-lg object-cover" loading="lazy">
                                        @else
                                            <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-slate-100 text-[9px] text-slate-400">Foto —</span>
                                        @endif
                                        <span class="min-w-0"><span class="block font-bold text-blue-700">#REP-{{ $item->id }}</span><span class="mt-1 block max-w-44 truncate font-semibold text-slate-800">{{ $item->judul }}</span><span class="mt-1 block truncate text-[10px] text-slate-500">{{ $item->nama_pelapor ?? 'Pengguna tidak ditemukan' }}</span></span>
                                    </div></td>
                                    <td><span class="block max-w-40 truncate font-semibold">{{ $item->kategori ?: 'Kategori belum tersedia' }}</span><span class="mt-1 block max-w-40 truncate text-[10px] text-slate-500">{{ $item->alamat_lokasi ?: 'Lokasi belum tersedia' }}</span></td>
                                    <td><span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $statusClass }}">{{ ucfirst($item->status) }}</span></td>
                                    <td class="whitespace-nowrap text-[10px] text-slate-600">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->translatedFormat('d M, H:i') : '-' }}</td>
                                    <td><a href="{{ route('admin.laporan.show', $item->id) }}" class="font-bold text-blue-700 hover:underline">Buka →</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada laporan yang sesuai dengan pencarian atau filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
                    <span>Menampilkan {{ $laporan->firstItem() ?? 0 }}–{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} laporan</span>
                    <div>{{ $laporan->links() }}</div>
                </div>
            </section>

            <aside id="selected-report-panel" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Detail laporan terpilih" data-map-bounds="{{ json_encode($regionBounds) }}" data-map-reports="{{ json_encode($laporanPeta) }}">
                @if ($activeReport)
                    <div class="bg-[#eef3ff] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2"><span id="detail-report-id" class="text-xs font-bold text-blue-700">#REP-{{ $activeReport->id }}</span><span id="detail-report-status" class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ match ($activeReport->status) { 'menunggu' => 'bg-red-100 text-red-700', 'diproses' => 'bg-blue-100 text-blue-700', 'selesai' => 'bg-emerald-100 text-emerald-700', default => 'bg-slate-100 text-slate-600' } }}">{{ ucfirst($activeReport->status) }}</span></div>
                        <h2 id="detail-report-title" class="mt-3 text-xl font-bold leading-snug">{{ $activeReport->judul }}</h2>
                        <p id="detail-report-address" class="mt-2 text-xs text-slate-600">{{ $activeReport->alamat_lokasi ?: 'Alamat belum tersedia' }}</p>
                    </div>
                    <div class="grid gap-4 p-5">
                        <section class="grid grid-cols-[40px_1fr] items-center gap-3 rounded-xl bg-slate-50 p-3" aria-label="Informasi pelapor">
                            <span class="grid size-10 place-items-center rounded-full bg-blue-100 font-bold text-blue-700">{{ strtoupper(substr($activeReport->nama_pelapor ?? 'P', 0, 1)) }}</span>
                            <div><p id="detail-reporter" class="text-xs font-bold">{{ $activeReport->nama_pelapor ?? 'Pengguna tidak ditemukan' }}</p><p id="detail-email" class="mt-1 text-[10px] text-slate-500">{{ $activeReport->email_pelapor ?? '-' }}</p></div>
                        </section>
                        <section><div class="mb-2 flex items-center justify-between text-[10px] font-bold uppercase tracking-wide"><span>Bukti foto kerusakan</span><span id="detail-category" class="normal-case text-blue-700">{{ $activeReport->kategori ?: 'Kategori belum tersedia' }}</span></div>
                            <img id="detail-report-photo" src="{{ $activeReport->foto ? asset('storage/' . $activeReport->foto) : '' }}" alt="Foto bukti kerusakan laporan" class="h-48 w-full rounded-xl object-cover" data-detail-photo @if (! $activeReport->foto) hidden @endif>
                            <p id="detail-no-photo" class="grid h-32 place-items-center rounded-xl bg-slate-100 text-xs text-slate-500" @if ($activeReport->foto) hidden @endif>Pelapor tidak melampirkan foto.</p>
                        </section>
                        <section><div class="mb-2 flex items-center justify-between text-[10px] font-bold uppercase tracking-wide"><span>Titik koordinat geospasial</span><span id="detail-coordinates" class="normal-case font-medium text-slate-500">{{ $activeReport->latitude }}, {{ $activeReport->longitude }}</span></div>
                            <div id="admin-laporan-map" class="h-44 overflow-hidden rounded-xl bg-slate-100" role="region" aria-label="Peta lokasi laporan terpilih"></div>
                            <p id="detail-no-location" class="mt-2 text-[11px] text-red-600" hidden>Lokasi laporan belum tersedia atau koordinatnya tidak valid.</p>
                        </section>
                        <section><h3 class="text-[10px] font-bold uppercase tracking-wide">Uraian laporan masyarakat</h3><p id="detail-report-description" class="mt-2 rounded-xl bg-[#eef3ff] p-4 text-xs leading-relaxed text-slate-700">{{ $activeReport->deskripsi ?: 'Pelapor tidak memberikan deskripsi.' }}</p><p class="mt-2 text-[10px] text-slate-500">Dilaporkan <span id="detail-created-at">{{ $activeReport->created_at ? \Illuminate\Support\Carbon::parse($activeReport->created_at)->translatedFormat('d M Y, H:i') : '-' }}</span></p></section>
                        <div class="grid gap-2">
                            <a id="detail-open-link" href="{{ route('admin.laporan.show', $activeReport->id) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-700 px-4 text-xs font-bold text-white hover:bg-blue-800">Buka detail lengkap</a>
                            <form id="detail-status-form" action="{{ route('admin.laporan.status.update', $activeReport->id) }}" method="POST" class="grid grid-cols-[1fr_auto] gap-2" @if ($activeReport->status === 'selesai') hidden @endif>
                                    @csrf
                                    @method('PATCH')
                                    <select id="detail-next-status" name="status" class="min-h-11 min-w-0 rounded-xl border border-slate-200 bg-white px-3 text-xs" aria-label="Status berikutnya" required>
                                        <option value="diproses">Verifikasi dan proses</option>
                                        <option value="selesai">Tandai selesai</option>
                                    </select>
                                    <button type="submit" class="min-h-11 rounded-xl bg-blue-700 px-4 text-xs font-bold text-white hover:bg-blue-800">Simpan</button>
                            </form>
                            <p id="detail-status-complete" class="rounded-xl bg-emerald-50 p-3 text-center text-xs font-semibold text-emerald-700" @if ($activeReport->status !== 'selesai') hidden @endif>Laporan ini telah selesai ditangani.</p>
                            <a href="{{ route('admin.penugasan.index') }}" class="text-center text-xs font-semibold text-blue-700 hover:underline">Buka penugasan tim lapangan →</a>
                        </div>
                    </div>
                @else
                    <div class="grid min-h-80 place-items-center p-8 text-center text-sm text-slate-500">Pilih laporan yang memiliki koordinat untuk melihat detail dan lokasinya.</div>
                @endif
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
    @vite('resources/js/admin-laporan.js')
@endpush
