@extends('superadmin.layouts.app')

@section('title', 'Seluruh Laporan')
@section('breadcrumb', 'Seluruh laporan')

@section('content')
    <section class="grid gap-5 text-slate-800">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Pemantauan laporan</p><h1 class="mt-1 text-2xl font-extrabold tracking-tight">Seluruh Laporan</h1><p class="mt-1 text-sm text-slate-500">Pantau laporan masyarakat di dalam wilayah Kabupaten Indramayu.</p></div>
            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-blue-700 hover:bg-blue-50">Lihat peta →</a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Daftar laporan masyarakat">
            <form method="GET" action="{{ route('superadmin.laporan.index') }}" class="grid gap-3 border-b border-slate-100 bg-white p-4 sm:grid-cols-[minmax(220px,1fr)_180px_220px_auto_auto] sm:items-end">
                <label class="grid gap-1.5 text-[11px] font-semibold text-slate-500">Cari laporan<input type="search" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Judul, kategori, atau pelapor" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500"></label>
                <label class="grid gap-1.5 text-[11px] font-semibold text-slate-500">Status<select name="status" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500"><option value="">Semua status</option>@foreach (['menunggu', 'diproses', 'selesai'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
                <label class="grid gap-1.5 text-[11px] font-semibold text-slate-500">Kategori<select name="kategori" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500"><option value="">Semua kategori</option>@foreach ($kategoriList as $kategori)<option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>@endforeach</select></label>
                <button type="submit" class="min-h-10 cursor-pointer rounded-xl bg-blue-600 px-4 text-xs font-bold text-white hover:bg-blue-700">Terapkan</button>
                @if (request()->hasAny(['cari', 'status', 'kategori']))<a href="{{ route('superadmin.laporan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">Reset</a>@endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[840px] text-left text-xs [&_th]:bg-[#f0f4ff] [&_th]:px-4 [&_th]:py-3 [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3.5">
                    <thead><tr><th scope="col">ID &amp; Laporan</th><th scope="col">Pelapor</th><th scope="col">Lokasi</th><th scope="col">Kategori</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            @php
                                $statusClass = match ($item->status) {
                                    'menunggu' => 'bg-amber-50 text-amber-700',
                                    'diproses' => 'bg-blue-50 text-blue-700',
                                    'selesai' => 'bg-emerald-50 text-emerald-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td><a href="{{ route('superadmin.laporan.show', $item) }}" class="flex min-w-56 items-center gap-3 text-inherit no-underline"><span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100">@if ($item->foto)<img src="{{ asset('storage/' . $item->foto) }}" alt="" class="size-full object-cover" loading="lazy">@else<span class="text-[9px] text-slate-400">Tanpa foto</span>@endif</span><span><span class="block font-bold text-blue-700">#SR-{{ $item->id }}</span><span class="mt-1 block max-w-52 truncate font-semibold text-slate-800">{{ $item->judul }}</span></span></a></td>
                                <td>{{ $item->pelapor?->name ?? 'Tidak tersedia' }}</td>
                                <td class="max-w-48 truncate text-slate-600">{{ $item->alamat_lokasi ?: 'Alamat belum tersedia' }}</td>
                                <td><span class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-[10px] font-semibold text-blue-700">{{ $item->kategori ?: 'Kategori belum tersedia' }}</span></td>
                                <td><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">{{ ucfirst($item->status) }}</span></td>
                                <td class="whitespace-nowrap text-slate-500">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('superadmin.laporan.show', $item) }}" class="font-semibold text-blue-700 hover:underline">Detail →</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Tidak ada laporan yang sesuai dengan pencarian atau filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 p-4 text-xs text-slate-500"><span>Menampilkan {{ $laporan->firstItem() ?? 0 }}–{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} laporan di wilayah pemantauan</span>{{ $laporan->links() }}</div>
        </section>
    </section>
@endsection
