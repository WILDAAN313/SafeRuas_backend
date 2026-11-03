@extends('admin.layouts.app')

@section('title', 'Statistik Laporan')
@section('breadcrumb', 'Statistik')

@section('content')
    <section class="grid gap-5 text-[#172b4d]">
        <div>
            <p class="text-[10px] font-bold tracking-[1.5px] text-blue-700">ANALISIS LAPORAN <span class="px-1 text-slate-400">•</span> ADMIN INSTANSI</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight">Statistik Laporan SafeRuas</h1>
            <p class="mt-1 text-sm text-slate-500">Ringkasan laporan dan penyelesaian penanganan pada instansi Anda.</p>
        </div>

        <form action="{{ route('admin.statistik.index') }}" method="GET" class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <label for="periode" class="text-xs font-semibold text-slate-600">Periode laporan</label>
            <input id="periode" type="month" name="periode" value="{{ $filters['periode'] ?? '' }}" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="min-h-10 rounded-xl bg-blue-700 px-4 text-xs font-bold text-white hover:bg-blue-800">Terapkan</button>
            @if (! empty($filters['periode']))
                <a href="{{ route('admin.statistik.index') }}" class="inline-flex min-h-10 items-center rounded-xl bg-[#eef3ff] px-4 text-xs font-semibold text-slate-700 hover:bg-blue-100">Reset periode</a>
            @endif
        </form>

        <div class="grid grid-cols-4 gap-3 max-[1000px]:grid-cols-2 max-[560px]:grid-cols-1">
            @foreach ([['Total Laporan', $totalLaporan, 'bg-blue-50 text-blue-700'], ['Menunggu', $jumlahMenunggu, 'bg-amber-50 text-amber-700'], ['Diproses', $jumlahDiproses, 'bg-indigo-50 text-indigo-700'], ['Selesai', $jumlahSelesai, 'bg-emerald-50 text-emerald-700']] as [$label, $jumlah, $accent])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p><span class="grid size-9 place-items-center rounded-xl {{ $accent }}" aria-hidden="true">●</span></div>
                    <p class="mt-4 text-3xl font-extrabold tracking-tight">{{ number_format($jumlah, 0, ',', '.') }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">Pada periode yang dipilih</p>
                </article>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="category-title">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 id="category-title" class="font-bold">Ringkasan Kategori Kerusakan</h2>
                <p class="mt-1 text-xs text-slate-500">Jumlah laporan dari database menurut kategori dan status saat ini.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($kategoriStatistik as $kategori)
                    <article class="grid gap-4 p-5 lg:grid-cols-[minmax(180px,1fr)_minmax(240px,1.2fr)_repeat(4,minmax(72px,auto))] lg:items-center">
                        <div><h3 class="text-sm font-bold">{{ $kategori->kategori }}</h3><p class="mt-1 text-[11px] text-slate-500">{{ number_format($kategori->total, 0, ',', '.') }} laporan</p></div>
                        <div class="grid gap-2" role="img" aria-label="{{ $kategori->kategori }}: {{ $kategori->menunggu }} menunggu, {{ $kategori->diproses }} diproses, {{ $kategori->selesai }} selesai">
                            <div class="flex h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <span class="bg-amber-500" style="width: {{ $kategori->menungguPersen }}%"></span>
                                <span class="bg-blue-600" style="width: {{ $kategori->diprosesPersen }}%"></span>
                                <span class="bg-emerald-500" style="width: {{ $kategori->selesaiPersen }}%"></span>
                            </div>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-slate-500">
                                <span><i class="mr-1 inline-block size-2 rounded-full bg-amber-500"></i>Menunggu</span>
                                <span><i class="mr-1 inline-block size-2 rounded-full bg-blue-600"></i>Diproses</span>
                                <span><i class="mr-1 inline-block size-2 rounded-full bg-emerald-500"></i>Selesai</span>
                            </div>
                        </div>
                        <div class="text-xs"><span class="block text-[9px] font-semibold uppercase text-slate-400">Total</span><strong>{{ number_format($kategori->total, 0, ',', '.') }}</strong></div>
                        <div class="text-xs text-amber-700"><span class="block text-[9px] font-semibold uppercase text-slate-400">Menunggu</span><strong>{{ number_format($kategori->menunggu, 0, ',', '.') }}</strong></div>
                        <div class="text-xs text-blue-700"><span class="block text-[9px] font-semibold uppercase text-slate-400">Diproses</span><strong>{{ number_format($kategori->diproses, 0, ',', '.') }}</strong></div>
                        <div class="text-xs text-emerald-700"><span class="block text-[9px] font-semibold uppercase text-slate-400">Selesai</span><strong>{{ number_format($kategori->selesai, 0, ',', '.') }}</strong></div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(280px,0.7fr)]" aria-labelledby="performance-title">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h2 id="performance-title" class="font-bold">Ringkasan Kinerja Unit</h2><p class="mt-1 text-xs text-slate-500">Hanya laporan berstatus selesai dihitung sebagai penanganan laporan yang tuntas.</p></div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-bold text-emerald-700">{{ number_format($persentaseSelesai, 1, ',', '.') }}% selesai</span>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    @foreach ($kategoriStatistik as $kategori)
                        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-600">{{ $kategori->kategori }} selesai</p><p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($kategori->selesai, 0, ',', '.') }}</p><p class="mt-1 text-[10px] text-slate-500">Laporan berstatus selesai</p></div>
                    @endforeach
                </div>
            </article>
            <article class="flex flex-col justify-center rounded-2xl border border-blue-100 bg-[#eef3ff] p-5">
                <p class="text-xs font-semibold text-slate-600">Total laporan selesai</p>
                <p class="mt-2 text-4xl font-extrabold text-blue-800">{{ number_format($jumlahSelesai, 0, ',', '.') }}</p>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-blue-700" style="width: {{ $persentaseSelesai }}%"></div></div>
                <p class="mt-3 text-[10px] leading-relaxed text-slate-600">Persentase dihitung dari laporan selesai dibanding total laporan pada periode yang dipilih. Status selesai tidak menjadi bukti bahwa perbaikan fisik telah diverifikasi.</p>
            </article>
        </section>
    </section>
@endsection
