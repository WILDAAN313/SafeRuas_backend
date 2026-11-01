@extends('admin.layouts.app')

@section('title', 'Penugasan Petugas')

@section('breadcrumb', 'Penugasan Petugas')

@section('content')
    <div class="text-[#172b4d]">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 [&_h1]:m-0 [&_h1]:mb-[7px] [&_h1]:text-[25px] [&_h1]:font-bold [&_h1]:tracking-[-0.6px] [&_h1]:max-[600px]:text-[21px] [&_p]:m-0 [&_p]:text-[13px] [&_p]:text-[#718096]">
            <div>
                <h1>Penugasan Petugas</h1>
                <p>Tugaskan petugas instansi untuk menangani laporan yang masih terbuka.</p>
            </div>
            <div class="rounded-xl border border-[#e5ebf4] bg-white px-[15px] py-[11px] text-[13px] text-[#52627a]">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-[#e8edf5] bg-white shadow-[0_4px_18px_rgba(25,55,99,0.035)]">
            <div class="border-b border-[#edf1f7] px-[22px] py-[21px] [&_h2]:m-0 [&_h2]:mb-1.5 [&_h2]:text-[17px] [&_h2]:font-bold [&_p]:m-0 [&_p]:text-xs [&_p]:text-[#8190a5] max-[600px]:p-[18px]">
                <h2>Laporan yang perlu ditangani</h2>
                <p>Menampilkan laporan berstatus menunggu atau diproses.</p>
            </div>

            @if ($petugas->isEmpty())
                <div class="border-b border-[#edf1f7] bg-[#f8faff] px-[18px] py-3 text-xs text-[#536178]">
                    Belum ada petugas aktif yang terhubung dengan instansi ini.
                </div>
            @endif

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse whitespace-nowrap [&_th]:bg-[#f8faff] [&_th]:px-[18px] [&_th]:py-[14px] [&_th]:text-left [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-[0.6px] [&_th]:text-[#718096] [&_td]:border-t [&_td]:border-[#edf1f7] [&_td]:px-[18px] [&_td]:py-4 [&_td]:align-middle [&_td]:text-xs [&_tbody_tr:hover]:bg-[#fafcff] min-w-[940px]">
                    <thead>
                        <tr>
                            <th>Laporan</th>
                            <th>Status</th>
                            <th>Petugas saat ini</th>
                            <th>Penugasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            @php
                                $penugasanAktif = $item->penugasan
                                    ->sortByDesc('tanggal_penugasan')
                                    ->first();
                                $statusClass = $item->status === 'diproses'
                                    ? 'bg-[#e5efff] text-[#245ec7]'
                                    : 'bg-[#fff4dc] text-[#a56800]';
                            @endphp
                            <tr>
                                <td>
                                    <div class="max-w-[230px] overflow-hidden text-ellipsis font-bold text-[#253957]">{{ $item->judul }}</div>
                                    <div class="mt-[5px] max-w-[230px] overflow-hidden text-ellipsis text-[11px] text-[#8a97aa]">
                                        #{{ $item->id }} · {{ ucfirst($item->kategori ?? 'Lainnya') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-[20px] px-2.5 py-1.5 text-[10px] font-bold {{ $statusClass }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($penugasanAktif?->petugas)
                                        <div class="font-semibold text-[#34445e]">{{ $penugasanAktif->petugas->name }}</div>
                                        <div class="mt-[5px] text-[11px] text-[#8a97aa]">{{ $penugasanAktif->petugas->email }}</div>
                                        <div class="mt-[5px] text-[11px] text-[#8a97aa]">
                                            Ditugaskan {{ $penugasanAktif->tanggal_penugasan?->format('d/m/Y H:i') ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-[11px] font-semibold text-[#a56800]">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($petugas->isNotEmpty())
                                        <form
                                            action="{{ route('admin.penugasan.store', $item->id) }}"
                                            method="POST"
                                            class="grid min-w-[230px] gap-2"
                                        >
                                            @csrf
                                            <label class="sr-only" for="petugas-{{ $item->id }}">
                                                Pilih petugas untuk laporan {{ $item->id }}
                                            </label>
                                            <select
                                                id="petugas-{{ $item->id }}"
                                                name="petugas_id"
                                                class="min-h-[42px] w-full min-w-0 rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10"
                                                required
                                            >
                                                <option value="">Pilih petugas</option>
                                                @foreach ($petugas as $orang)
                                                    <option
                                                        value="{{ $orang->id }}"
                                                        @selected($penugasanAktif !== null && $penugasanAktif->petugas_id == $orang->id)
                                                    >
                                                        {{ $orang->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label class="sr-only" for="catatan-{{ $item->id }}">
                                                Catatan penugasan untuk laporan {{ $item->id }}
                                            </label>
                                            <input
                                                id="catatan-{{ $item->id }}"
                                                type="text"
                                                name="catatan"
                                                class="min-h-[42px] w-full min-w-0 rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10"
                                                maxlength="1000"
                                                value="{{ $penugasanAktif?->catatan }}"
                                                placeholder="Catatan (opsional)"
                                            >
                                            <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1">
                                                {{ $penugasanAktif ? 'Simpan perubahan' : 'Tugaskan' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="mt-[5px] text-[11px] text-[#8a97aa]">Tambahkan petugas pada instansi ini terlebih dahulu.</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="!whitespace-normal !px-5 !py-[55px] !text-center !text-[#8794a8]">
                                    <div class="mb-1.5 font-bold text-[#34445e]">Tidak ada laporan terbuka</div>
                                    <div>Semua laporan instansi ini sudah selesai atau belum ada laporan masuk.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-[15px] border-t border-[#edf1f7] px-[22px] py-[17px] text-xs text-[#718096] max-[600px]:p-[15px]">
                <div>
                    Menampilkan {{ $laporan->firstItem() ?? 0 }} sampai {{ $laporan->lastItem() ?? 0 }}
                    dari {{ $laporan->total() }} laporan
                </div>
                <div class="[&_nav>div:first-child]:hidden [&_nav>div:last-child]:m-0 [&_svg]:h-4 [&_svg]:w-4">{{ $laporan->links() }}</div>
            </div>
        </section>
    </div>
@endsection
