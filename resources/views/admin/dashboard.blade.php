@extends('admin.layouts.app')

@section('title', 'Dashboard Admin Instansi')

@section('breadcrumb', 'Dashboard')

@section('content')
    <section class="text-[#172b4d]">
        <div class="mb-[22px] flex items-center justify-between gap-5 max-[500px]:items-start max-[500px]:flex-col">
            <div>
                <div class="mb-2 text-[#2563eb] text-[9px] font-extrabold tracking-[1.5px]">RINGKASAN INSTANSI</div>
                <h1 class="m-0 text-[26px] font-extrabold max-[500px]:text-[22px]">Dashboard Admin</h1>
                <p class="mt-2 text-[#7d8aa0] text-xs leading-[1.7]">Pantau laporan infrastruktur dan progres penanganannya.</p>
            </div>

            <div class="rounded-[10px] border border-[#e8edf5] bg-white px-[14px] py-2.5 text-[11px] text-[#66758c] whitespace-nowrap">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>

        <div class="mb-[22px] grid grid-cols-4 gap-4 max-[1050px]:grid-cols-2 max-[600px]:gap-2.5">
            <article class="min-w-0 rounded-[15px] border border-[#e8edf5] bg-white p-[19px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[14px]">
                <div class="flex items-center justify-between gap-2.5">
                    <span class="text-[11px] font-semibold text-[#718096]">Total laporan</span>
                    <span class="grid h-[34px] w-[34px] place-items-center rounded-[10px] text-[17px] font-bold bg-[#eaf1ff] text-[#2563eb]" aria-hidden="true">▤</span>
                </div>
                <div class="mt-[14px] text-[30px] font-extrabold leading-[1.1] tracking-[-1px] text-[#172b4d] max-[600px]:text-[25px]">{{ number_format($totalLaporan) }}</div>
                <div class="mt-2 text-[10px] text-[#8b97a9]">Seluruh laporan yang tercatat</div>
            </article>

            <article class="min-w-0 rounded-[15px] border border-[#e8edf5] bg-white p-[19px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[14px]">
                <div class="flex items-center justify-between gap-2.5">
                    <span class="text-[11px] font-semibold text-[#718096]">Menunggu</span>
                    <span class="grid h-[34px] w-[34px] place-items-center rounded-[10px] text-[17px] font-bold bg-[#fff5df] text-[#b77908]" aria-hidden="true">◷</span>
                </div>
                <div class="mt-[14px] text-[30px] font-extrabold leading-[1.1] tracking-[-1px] text-[#172b4d] max-[600px]:text-[25px]">{{ number_format($laporanMenunggu) }}</div>
                <div class="mt-2 text-[10px] text-[#8b97a9]">Menunggu tindak lanjut</div>
            </article>

            <article class="min-w-0 rounded-[15px] border border-[#e8edf5] bg-white p-[19px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[14px]">
                <div class="flex items-center justify-between gap-2.5">
                    <span class="text-[11px] font-semibold text-[#718096]">Diproses</span>
                    <span class="grid h-[34px] w-[34px] place-items-center rounded-[10px] text-[17px] font-bold bg-[#eeedff] text-[#4f46e5]" aria-hidden="true">↻</span>
                </div>
                <div class="mt-[14px] text-[30px] font-extrabold leading-[1.1] tracking-[-1px] text-[#172b4d] max-[600px]:text-[25px]">{{ number_format($laporanDiproses) }}</div>
                <div class="mt-2 text-[10px] text-[#8b97a9]">Sedang ditangani petugas</div>
            </article>

            <article class="min-w-0 rounded-[15px] border border-[#e8edf5] bg-white p-[19px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[14px]">
                <div class="flex items-center justify-between gap-2.5">
                    <span class="text-[11px] font-semibold text-[#718096]">Selesai</span>
                    <span class="grid h-[34px] w-[34px] place-items-center rounded-[10px] text-[17px] font-bold bg-[#e7f8ef] text-[#16845e]" aria-hidden="true">✓</span>
                </div>
                <div class="mt-[14px] text-[30px] font-extrabold leading-[1.1] tracking-[-1px] text-[#172b4d] max-[600px]:text-[25px]">{{ number_format($laporanSelesai) }}</div>
                <div class="mt-2 text-[10px] text-[#8b97a9]">Penanganan telah selesai</div>
            </article>
        </div>

        <section class="min-w-0 overflow-hidden rounded-[15px] border border-[#e8edf5] bg-white mb-5">
            <div class="flex items-center justify-between gap-4 border-b border-[#edf1f7] px-[22px] py-5 max-[600px]:flex-col max-[600px]:items-start max-[600px]:p-4">
                <div>
                    <h2 class="m-0 text-sm font-bold text-[#253957]">Laporan terbaru</h2>
                    <p class="mt-[5px] mb-0 text-[10px] text-[#8b97a9]">Lima laporan terakhir yang masuk ke sistem.</p>
                </div>
                <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center gap-2 rounded-[9px] border border-transparent px-[15px] py-[11px] text-[11px] font-bold border-[#e1e8f2] bg-white text-[#536178]">
                    Lihat semua laporan
                </a>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse whitespace-nowrap [&_th]:bg-[#f8faff] [&_th]:px-[17px] [&_th]:py-[13px] [&_th]:text-left [&_th]:text-[9px] [&_th]:font-extrabold [&_th]:uppercase [&_th]:tracking-[0.6px] [&_th]:text-[#718096] [&_td]:border-t [&_td]:border-[#edf1f7] [&_td]:px-[17px] [&_td]:py-[14px] [&_td]:align-middle [&_td]:text-[11px] [&_td]:text-[#52627a] [&_tbody_tr:hover]:bg-[#fafcff]">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Laporan</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporanTerbaru as $laporan)
                            @php
                                $statusClass = match ($laporan->status) {
                                    'menunggu' => 'bg-[#fff4dc] text-[#a56800]',
                                    'diproses' => 'bg-[#e5efff] text-[#245ec7]',
                                    'selesai' => 'bg-[#dcfce7] text-[#15803d]',
                                    default => 'bg-[#edf1f7] text-[#52627a]',
                                };
                            @endphp
                            <tr>
                                <td class="!text-[10px] !text-[#8290a5]">#{{ $laporan->id }}</td>
                                <td>
                                    <div class="max-w-[260px] overflow-hidden text-ellipsis font-bold text-[#253957]">{{ $laporan->judul }}</div>
                                    <div class="mt-1 max-w-[260px] overflow-hidden text-ellipsis text-[10px] text-[#8b97a9]">
                                        {{ \Illuminate\Support\Str::limit($laporan->deskripsi ?? 'Tidak ada deskripsi', 72) }}
                                    </div>
                                </td>
                                <td>{{ ucfirst($laporan->kategori ?? 'Lainnya') }}</td>
                                <td>{{ $laporan->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td>
                                    <span class="inline-flex items-center rounded-[20px] px-[9px] py-[5px] text-[9px] font-bold {{ $statusClass }}">
                                        {{ ucfirst($laporan->status ?? 'Tidak diketahui') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.laporan.show', $laporan->id) }}" class="text-[10px] font-bold text-[#2563eb] hover:text-[#1d4ed8] hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="!whitespace-normal !px-[18px] !py-[42px] !text-center !text-[#8794a8] [&_strong]:mb-[5px] [&_strong]:block [&_strong]:text-[#34445e] [&_span]:block">
                                    <strong>Belum ada laporan</strong>
                                    <span>Laporan terbaru akan tampil di sini setelah masyarakat mengirimkannya.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
