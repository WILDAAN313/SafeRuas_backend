@extends('superadmin.layouts.app')

@section('title', 'Detail Masyarakat')
@section('breadcrumb', 'Masyarakat / Detail')

@section('content')
    <section class="grid gap-5 text-slate-800">
        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Profil masyarakat</p><h1 class="mt-1 text-2xl font-extrabold tracking-tight">{{ $masyarakat->name }}</h1><p class="mt-1 text-sm text-slate-500">Informasi akun dan laporan yang dibuat.</p></div><a href="{{ route('superadmin.masyarakat.index') }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">← Kembali ke masyarakat</a></div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="identity-title">
            <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 pb-5"><span class="grid size-14 place-items-center rounded-full bg-blue-50 text-xl font-extrabold text-blue-700">{{ mb_strtoupper(mb_substr($masyarakat->name, 0, 1)) }}</span><div><h2 id="identity-title" class="font-bold">Informasi pengguna</h2><p class="mt-1 text-sm text-slate-500">{{ $masyarakat->email }}</p></div><span class="ml-auto rounded-full px-3 py-1.5 text-[10px] font-semibold {{ $masyarakat->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $masyarakat->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
            <dl class="mt-5 grid gap-5 text-sm sm:grid-cols-3"><div><dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Nama</dt><dd class="mt-1 font-semibold">{{ $masyarakat->name }}</dd></div><div><dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 break-words">{{ $masyarakat->email }}</dd></div><div><dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Terdaftar sejak</dt><dd class="mt-1">{{ $masyarakat->created_at?->format('d M Y') ?? '-' }}</dd></div><div><dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Status akun</dt><dd class="mt-1">{{ $masyarakat->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div><div><dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Jumlah laporan</dt><dd class="mt-1 font-bold text-blue-700">{{ number_format($laporan->total(), 0, ',', '.') }}</dd></div></dl>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="reports-title">
            <div class="border-b border-slate-100 p-5"><h2 id="reports-title" class="font-bold">Riwayat laporan</h2><p class="mt-1 text-xs text-slate-500">Laporan masyarakat dalam wilayah pemantauan.</p></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[680px] text-left text-xs [&_th]:bg-[#f0f4ff] [&_th]:px-4 [&_th]:py-3 [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-slate-500 [&_td]:border-t [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-4"><thead><tr><th scope="col">Laporan</th><th scope="col">Instansi</th><th scope="col">Status</th><th scope="col">Tanggal</th><th scope="col">Aksi</th></tr></thead><tbody>
                @forelse ($laporan as $item)
                    @php($statusClass = match ($item->status) { 'menunggu' => 'bg-amber-50 text-amber-700', 'diproses' => 'bg-blue-50 text-blue-700', 'selesai' => 'bg-emerald-50 text-emerald-700', default => 'bg-slate-100 text-slate-600' })
                    <tr class="hover:bg-slate-50/70"><td><p class="font-semibold">{{ $item->judul }}</p><p class="mt-1 text-[10px] text-slate-500">{{ $item->kategori }}</p></td><td>{{ $item->instansi?->nama_instansi ?? 'Belum ditentukan' }}</td><td><span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">{{ ucfirst($item->status) }}</span></td><td class="whitespace-nowrap text-slate-500">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td><td><a href="{{ route('superadmin.laporan.show', $item) }}" class="font-semibold text-blue-700 hover:underline">Detail laporan →</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Pengguna ini belum membuat laporan.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="border-t border-slate-100 p-4">{{ $laporan->links() }}</div>
        </section>
    </section>
@endsection
