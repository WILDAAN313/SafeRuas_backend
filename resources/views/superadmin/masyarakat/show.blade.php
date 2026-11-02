@extends('superadmin.layouts.app')

@section('title', 'Detail Masyarakat')
@section('breadcrumb', 'Detail masyarakat')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4"><div><h1 class="text-2xl font-extrabold">{{ $masyarakat->name }}</h1><p class="mt-2 text-sm text-slate-500">Identitas masyarakat dan riwayat laporan.</p></div><a href="{{ route('superadmin.masyarakat.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600">Kembali ke daftar</a></div>
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="identity-title"><h2 id="identity-title" class="font-bold">Informasi pengguna</h2><dl class="mt-5 grid gap-5 text-sm sm:grid-cols-2 xl:grid-cols-4"><div><dt class="text-xs text-slate-400">Email</dt><dd class="mt-1 break-words">{{ $masyarakat->email }}</dd></div><div><dt class="text-xs text-slate-400">Terdaftar sejak</dt><dd class="mt-1">{{ $masyarakat->created_at?->format('d/m/Y') ?? '-' }}</dd></div><div><dt class="text-xs text-slate-400">Status akun</dt><dd class="mt-1">{{ $masyarakat->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div><div><dt class="text-xs text-slate-400">Jumlah laporan</dt><dd class="mt-1 font-bold">{{ number_format($laporan->total()) }}</dd></div></dl></section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" aria-labelledby="reports-title">
        <h2 id="reports-title" class="border-b border-slate-100 p-5 font-bold">Riwayat laporan</h2>
        <div class="overflow-x-auto"><table class="w-full text-left text-xs [&_th]:px-5 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-slate-400 [&_td]:px-5 [&_td]:py-4"><thead class="bg-slate-50"><tr><th scope="col">Laporan</th><th scope="col">Instansi</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($laporan as $item)
                <tr><td class="min-w-48"><p class="font-semibold">{{ $item->judul }}</p><p class="mt-1 text-slate-400">{{ $item->kategori }}</p></td><td>{{ $item->instansi?->nama_instansi ?? 'Belum ditentukan' }}</td><td>{{ ucfirst($item->status) }}</td><td class="whitespace-nowrap">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td><td><a href="{{ route('superadmin.laporan.show', $item) }}" class="font-semibold text-blue-700 hover:underline">Detail laporan</a></td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400">Pengguna ini belum membuat laporan.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 p-5">{{ $laporan->links() }}</div>
    </section>
@endsection
