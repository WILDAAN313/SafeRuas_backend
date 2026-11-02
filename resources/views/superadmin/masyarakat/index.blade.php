@extends('superadmin.layouts.app')

@section('title', 'Data Masyarakat')
@section('breadcrumb', 'Masyarakat')

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-extrabold">Data masyarakat</h1><p class="mt-2 text-sm text-slate-500">Pantau pengguna terdaftar dan riwayat laporan yang dibuat.</p></div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" aria-label="Daftar masyarakat">
        <form method="GET" action="{{ route('superadmin.masyarakat.index') }}" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-5"><label class="grid min-w-48 flex-1 gap-2 text-xs font-semibold text-slate-500">Cari masyarakat<input type="search" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Nama atau email masyarakat" class="rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700"></label><button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Cari</button>@if (request()->filled('cari'))<a href="{{ route('superadmin.masyarakat.index') }}" class="px-3 py-2.5 text-xs font-semibold text-slate-500 hover:text-blue-700">Reset</a>@endif</form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs [&_th]:px-5 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-400 [&_td]:px-5 [&_td]:py-4">
                <thead class="bg-slate-50"><tr><th scope="col">Masyarakat</th><th scope="col">Jumlah laporan</th><th scope="col">Terdaftar</th><th scope="col">Status akun</th><th scope="col">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($masyarakat as $pengguna)
                        <tr><td class="min-w-48"><p class="font-semibold">{{ $pengguna->name }}</p><p class="mt-1 text-slate-400">{{ $pengguna->email }}</p></td><td>{{ number_format($pengguna->laporan_count) }}</td><td class="whitespace-nowrap">{{ $pengguna->created_at?->format('d/m/Y') ?? '-' }}</td><td>{{ $pengguna->is_active ? 'Aktif' : 'Nonaktif' }}</td><td><a href="{{ route('superadmin.masyarakat.show', $pengguna) }}" class="font-semibold text-blue-700 hover:underline">Detail</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400">Belum ada masyarakat yang sesuai dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-5">{{ $masyarakat->links() }}</div>
    </section>
@endsection
