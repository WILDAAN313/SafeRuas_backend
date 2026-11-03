@extends('superadmin.layouts.app')

@section('title', 'Masyarakat')
@section('breadcrumb', 'Masyarakat')

@section('content')
    <section class="grid gap-5 text-slate-800">
        <div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Manajemen pengguna</p><h1 class="mt-1 text-2xl font-extrabold tracking-tight">Masyarakat Terdaftar</h1><p class="mt-1 text-sm text-slate-500">Pantau akun masyarakat dan laporan yang tercatat pada aplikasi SafeRuas.</p></div>

        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ([['Total masyarakat', $ringkasan->total, 'bg-blue-50 text-blue-700'], ['Akun aktif', $ringkasan->aktif, 'bg-emerald-50 text-emerald-700'], ['Akun nonaktif', $ringkasan->nonaktif, 'bg-slate-100 text-slate-600']] as [$label, $jumlah, $warna])
                <article class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($jumlah, 0, ',', '.') }}</p></div><span class="grid size-10 place-items-center rounded-xl {{ $warna }}" aria-hidden="true">●</span></article>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Daftar masyarakat">
            <form method="GET" action="{{ route('superadmin.masyarakat.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-[minmax(220px,1fr)_190px_auto_auto] sm:items-end">
                <label class="grid gap-1.5 text-[11px] font-semibold text-slate-500">Cari masyarakat<input type="search" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Nama atau email" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500"></label>
                <label class="grid gap-1.5 text-[11px] font-semibold text-slate-500">Status akun<select name="status" class="min-h-10 rounded-xl border-0 bg-[#eef3ff] px-3 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500"><option value="">Semua status</option><option value="aktif" @selected(request('status') === 'aktif')>Aktif</option><option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option></select></label>
                <button type="submit" class="min-h-10 cursor-pointer rounded-xl bg-blue-600 px-4 text-xs font-bold text-white hover:bg-blue-700">Terapkan</button>
                @if (request()->hasAny(['cari', 'status']))<a href="{{ route('superadmin.masyarakat.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">Reset</a>@endif
            </form>
            <div class="overflow-x-auto"><table class="w-full min-w-[680px] text-left text-xs [&_th]:bg-[#f0f4ff] [&_th]:px-4 [&_th]:py-3 [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-4">
                <thead><tr><th scope="col">Pengguna</th><th scope="col">Laporan</th><th scope="col">Terdaftar</th><th scope="col">Status akun</th><th scope="col">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($masyarakat as $pengguna)
                        <tr class="hover:bg-slate-50/70"><td><div class="flex items-center gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-50 font-bold text-blue-700">{{ mb_strtoupper(mb_substr($pengguna->name, 0, 1)) }}</span><span class="min-w-0"><span class="block font-semibold text-slate-800">{{ $pengguna->name }}</span><span class="mt-1 block truncate text-slate-500">{{ $pengguna->email }}</span></span></div></td><td><span class="rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">{{ number_format($pengguna->laporan_count, 0, ',', '.') }} laporan</span></td><td class="whitespace-nowrap text-slate-500">{{ $pengguna->created_at?->format('d M Y') ?? '-' }}</td><td><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $pengguna->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $pengguna->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><a href="{{ route('superadmin.masyarakat.show', $pengguna) }}" class="font-semibold text-blue-700 hover:underline">Detail →</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada masyarakat yang sesuai dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 p-4 text-xs text-slate-500"><span>Menampilkan {{ $masyarakat->firstItem() ?? 0 }}–{{ $masyarakat->lastItem() ?? 0 }} dari {{ $masyarakat->total() }} masyarakat</span>{{ $masyarakat->links() }}</div>
        </section>
    </section>
@endsection
