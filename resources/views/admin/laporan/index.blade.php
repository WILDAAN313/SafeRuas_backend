
@extends('admin.layouts.app')

@section('title', 'Data Laporan')

@section('content')
<div class="text-[#172b4d]">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 [&_h1]:m-0 [&_h1]:mb-[7px] [&_h1]:text-[25px] [&_h1]:font-bold [&_h1]:tracking-[-0.6px] [&_h1]:max-[600px]:text-[21px] [&_p]:m-0 [&_p]:text-[13px] [&_p]:text-[#718096]">
        <div>
            <h1>Data Laporan Infrastruktur</h1>
            <p>Kelola dan pantau laporan kerusakan infrastruktur masyarakat.</p>
        </div>

        <div class="rounded-xl border border-[#e5ebf4] bg-white px-[15px] py-[11px] text-[13px] text-[#52627a]">
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <div class="mb-6 grid grid-cols-4 gap-4 max-[1000px]:grid-cols-2 max-[600px]:gap-2.5">
        <div class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
            <div class="mb-3 text-xs text-[#718096]">Total Laporan</div>
            <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl">{{ number_format($totalLaporan) }}</div>
            <div class="mt-2 text-[11px] text-[#8b97a9]">Seluruh laporan masuk</div>
        </div>

        <div class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
            <div class="mb-3 text-xs text-[#718096]">Menunggu Verifikasi</div>
            <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#d18a09]">
                {{ number_format($menunggu) }}
            </div>
            <div class="mt-2 text-[11px] text-[#8b97a9]">Laporan menunggu penanganan</div>
        </div>

        <div class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
            <div class="mb-3 text-xs text-[#718096]">Sedang Diproses</div>
            <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#2563eb]">
                {{ number_format($diproses) }}
            </div>
            <div class="mt-2 text-[11px] text-[#8b97a9]">Laporan dalam penanganan</div>
        </div>

        <div class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
            <div class="mb-3 text-xs text-[#718096]">Selesai</div>
            <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#16a34a]">
                {{ number_format($selesai) }}
            </div>
            <div class="mt-2 text-[11px] text-[#8b97a9]">Laporan telah diselesaikan</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#e8edf5] bg-white shadow-[0_4px_18px_rgba(25,55,99,0.035)]">
        <div class="border-b border-[#edf1f7] px-[22px] py-[21px] [&_h2]:m-0 [&_h2]:mb-1.5 [&_h2]:text-[17px] [&_h2]:font-bold [&_p]:m-0 [&_p]:text-xs [&_p]:text-[#8190a5] max-[600px]:p-[18px]">
            <h2>Daftar Laporan</h2>
            <p>Temukan laporan berdasarkan judul, pelapor, kategori, atau status.</p>
        </div>

        <form
            action="{{ route('admin.laporan.index') }}"
            method="GET"
            class="flex flex-wrap gap-2.5 border-b border-[#edf1f7] px-[22px] py-[18px] max-[600px]:p-[14px]"
            data-laporan-search
        >
            <input
                type="search"
                name="cari"
                class="min-h-[42px] min-w-[190px] flex-1 rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10 max-[600px]:w-full max-[600px]:min-w-0"
                placeholder="Cari judul, pelapor, atau lokasi..."
                value="{{ request('cari') }}"
            >

            <select name="status" class="min-h-[42px] min-w-[145px] rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10 max-[600px]:w-full max-[600px]:min-w-0">
                <option value="">Semua Status</option>
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>

            <select name="kategori" class="min-h-[42px] min-w-[145px] rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10 max-[600px]:w-full max-[600px]:min-w-0">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriList as $kategori)
                    <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>
                        {{ ucfirst($kategori) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1">Cari Laporan</button>

            <a href="{{ route('admin.laporan.index') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border border-[#dfe6f0] px-[14px] py-2.5 text-xs text-[#52627a] no-underline hover:bg-[#f5f8fc] max-[600px]:flex-1">
                Reset
            </a>
        </form>

        <div class="w-full overflow-x-auto">
            <table class="w-full border-collapse whitespace-nowrap [&_th]:bg-[#f8faff] [&_th]:px-[18px] [&_th]:py-[14px] [&_th]:text-left [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-[0.6px] [&_th]:text-[#718096] [&_td]:border-t [&_td]:border-[#edf1f7] [&_td]:px-[18px] [&_td]:py-4 [&_td]:align-middle [&_td]:text-xs [&_tbody_tr:hover]:bg-[#fafcff]">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Informasi Laporan</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($laporan as $item)
                        @php
                            $statusClass = match ($item->status) {
                                'menunggu' => 'bg-[#fff4dc] text-[#a56800]',
                                'diproses' => 'bg-[#e5efff] text-[#245ec7]',
                                'selesai' => 'bg-[#dcfce7] text-[#15803d]',
                                default => 'bg-[#edf1f7] text-[#52627a]',
                            };

                            $statusLabel = match ($item->status) {
                                'menunggu' => 'Menunggu',
                                'diproses' => 'Diproses',
                                'selesai' => 'Selesai',
                                default => ucfirst($item->status ?? 'Tidak diketahui'),
                            };
                        @endphp

                        <tr>
                            <td><span class="text-[11px] text-[#718096]">#{{ $item->id }}</span></td>

                            <td>
                                <div class="max-w-[230px] overflow-hidden text-ellipsis font-bold text-[#253957]">{{ $item->judul }}</div>
                                <div class="mt-[5px] max-w-[230px] overflow-hidden text-ellipsis text-[11px] text-[#8a97aa]">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi ?? 'Tidak ada deskripsi', 65) }}
                                </div>
                            </td>

                            <td>
                                <div class="font-semibold text-[#34445e]">
                                    {{ $item->nama_pelapor ?? 'Pengguna tidak ditemukan' }}
                                </div>
                                <div class="mt-[5px] text-[11px] text-[#8a97aa]">
                                    {{ $item->email_pelapor ?? '-' }}
                                </div>
                            </td>

                            <td>{{ ucfirst($item->kategori ?? 'Lainnya') }}</td>

                            <td>
                                <div class="max-w-[190px] overflow-hidden text-ellipsis text-[#65758c]">
                                    {{ $item->alamat_lokasi ?: 'Lokasi belum dicantumkan' }}
                                </div>
                            </td>

                            <td>
                                {{ $item->created_at
                                    ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td>
                                <span class="inline-flex items-center rounded-[20px] px-2.5 py-1.5 text-[10px] font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.laporan.show', $item->id) }}"
                                    class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!whitespace-normal !px-5 !py-[55px] !text-center !text-[#8794a8]">
                                <div class="mb-1.5 font-bold text-[#34445e]">
                                    Belum ada laporan yang ditemukan
                                </div>
                                <div>
                                    Coba ubah kata pencarian atau filter yang digunakan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-[15px] border-t border-[#edf1f7] px-[22px] py-[17px] text-xs text-[#718096] max-[600px]:p-[15px]">
            <div>
                Menampilkan {{ $laporan->firstItem() ?? 0 }}
                sampai {{ $laporan->lastItem() ?? 0 }}
                dari {{ $laporan->total() }} laporan
            </div>

            <div class="[&_nav>div:first-child]:hidden [&_nav>div:last-child]:m-0 [&_svg]:h-4 [&_svg]:w-4">
                {{ $laporan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/admin-laporan.js')
@endpush
