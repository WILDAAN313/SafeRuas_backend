@extends('superadmin.layouts.app')

@section('title', 'Seluruh Laporan')
@section('breadcrumb', 'Seluruh laporan')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4"><div><h1 class="text-2xl font-extrabold">Seluruh laporan</h1><p class="mt-2 text-sm text-slate-500">Pantau laporan masyarakat dari seluruh instansi.</p></div><a href="{{ route('superadmin.dashboard') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-blue-700">Lihat peta</a></div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <form method="GET" action="{{ route('superadmin.laporan.index') }}" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-5">
            <label class="grid min-w-48 flex-1 gap-2 text-xs font-semibold text-slate-500">Cari laporan<input type="search" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Judul, kategori, atau pelapor" class="rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700"></label>
            <label class="grid gap-2 text-xs font-semibold text-slate-500">Status<select name="status" class="rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700"><option value="">Semua status</option>@foreach (['menunggu', 'diproses', 'selesai'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-xs font-semibold text-slate-500">Kategori<select name="kategori" class="rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700"><option value="">Semua kategori</option>@foreach ($kategoriList as $kategori)<option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>@endforeach</select></label>
            <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Terapkan</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs [&_th]:px-5 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-400 [&_td]:px-5 [&_td]:py-4">
                <thead class="bg-slate-50"><tr><th scope="col">Laporan</th><th scope="col">Pelapor</th><th scope="col">Lokasi</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($laporan as $item)
                        <tr><td class="min-w-48"><p class="font-semibold">{{ $item->judul }}</p><p class="mt-1 text-slate-400">{{ $item->kategori }}</p></td><td>{{ $item->pelapor?->name ?? 'Tidak tersedia' }}</td><td class="min-w-40">{{ $item->alamat_lokasi ?? 'Alamat belum tersedia' }}</td><td>{{ ucfirst($item->status) }}</td><td class="whitespace-nowrap">{{ $item->created_at?->format('d/m/Y') }}</td><td><a href="{{ route('superadmin.laporan.show', $item) }}" class="font-semibold text-blue-700 hover:underline">Detail</a></td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400">Tidak ada laporan yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-5">{{ $laporan->links() }}</div>
    </section>
@endsection
