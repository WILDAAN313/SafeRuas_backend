@extends('admin.layouts.app')

@section('title', 'Statistik Laporan')

@section('breadcrumb', 'Statistik')

@section('content')
    <div class="text-[#172b4d]">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 [&_h1]:m-0 [&_h1]:mb-[7px] [&_h1]:text-[25px] [&_h1]:font-bold [&_h1]:tracking-[-0.6px] [&_h1]:max-[600px]:text-[21px] [&_p]:m-0 [&_p]:text-[13px] [&_p]:text-[#718096]">
            <div>
                <h1>Statistik Laporan</h1>
                <p>Ringkasan laporan pada instansi Anda berdasarkan status dan kategori.</p>
            </div>
            <div class="rounded-xl border border-[#e5ebf4] bg-white px-[15px] py-[11px] text-[13px] text-[#52627a]">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>

        <form action="{{ route('admin.statistik.index') }}" method="GET" class="mb-5 flex flex-wrap items-center gap-2.5 rounded-[14px] border border-[#e8edf5] bg-white px-[18px] py-[15px] max-[600px]:items-stretch max-[600px]:flex-col [&_label]:text-xs [&_label]:font-semibold [&_label]:text-[#52627a]">
            <label for="periode">Periode laporan</label>
            <input
                id="periode"
                type="month"
                name="periode"
                class="min-h-[42px] w-[190px] max-w-full flex-[0_1_190px] rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10 max-[600px]:w-full max-[600px]:min-w-0 max-[600px]:flex-auto"
                value="{{ $filters['periode'] ?? '' }}"
            >
            <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1">Terapkan</button>
            @if (! empty($filters['periode']))
                <a href="{{ route('admin.statistik.index') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border border-[#dfe6f0] px-[14px] py-2.5 text-xs text-[#52627a] no-underline hover:bg-[#f5f8fc] max-[600px]:flex-1">Reset</a>
            @endif
        </form>

        <section class="mb-5 grid grid-cols-4 gap-4 max-[1000px]:grid-cols-2 max-[600px]:gap-2.5">
            <article class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
                <div class="mb-3 text-xs text-[#718096]">Total laporan</div>
                <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl">{{ number_format($totalLaporan) }}</div>
                <div class="mt-2 text-[11px] text-[#8b97a9]">Pada periode yang dipilih</div>
            </article>
            <article class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
                <div class="mb-3 text-xs text-[#718096]">Menunggu</div>
                <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#b77908]">{{ number_format($jumlahMenunggu) }}</div>
                <progress class="mt-3 block h-2 w-full appearance-none overflow-hidden rounded-[20px] border-0 bg-[#edf1f7] [&::-webkit-progress-bar]:rounded-[20px] [&::-webkit-progress-bar]:bg-[#edf1f7] [&::-webkit-progress-value]:rounded-[20px] [&::-webkit-progress-value]:bg-[#2563eb] [&::-moz-progress-bar]:rounded-[20px] [&::-moz-progress-bar]:bg-[#2563eb] [&::-webkit-progress-value]:bg-[#d18a09] [&::-moz-progress-bar]:bg-[#d18a09]" value="{{ $jumlahMenunggu }}" max="{{ max(1, $totalLaporan) }}">
                    {{ $jumlahMenunggu }}
                </progress>
            </article>
            <article class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
                <div class="mb-3 text-xs text-[#718096]">Diproses</div>
                <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#2563eb]">{{ number_format($jumlahDiproses) }}</div>
                <progress class="mt-3 block h-2 w-full appearance-none overflow-hidden rounded-[20px] border-0 bg-[#edf1f7] [&::-webkit-progress-bar]:rounded-[20px] [&::-webkit-progress-bar]:bg-[#edf1f7] [&::-webkit-progress-value]:rounded-[20px] [&::-webkit-progress-value]:bg-[#2563eb] [&::-moz-progress-bar]:rounded-[20px] [&::-moz-progress-bar]:bg-[#2563eb] [&::-webkit-progress-value]:bg-[#2563eb] [&::-moz-progress-bar]:bg-[#2563eb]" value="{{ $jumlahDiproses }}" max="{{ max(1, $totalLaporan) }}">
                    {{ $jumlahDiproses }}
                </progress>
            </article>
            <article class="rounded-2xl border border-[#e8edf5] bg-white p-5 shadow-[0_4px_18px_rgba(25,55,99,0.035)] max-[600px]:p-[15px]">
                <div class="mb-3 text-xs text-[#718096]">Selesai</div>
                <div class="text-[29px] font-bold leading-[1.2] tracking-[-0.8px] max-[600px]:text-2xl text-[#16a34a]">{{ number_format($jumlahSelesai) }}</div>
                <progress class="mt-3 block h-2 w-full appearance-none overflow-hidden rounded-[20px] border-0 bg-[#edf1f7] [&::-webkit-progress-bar]:rounded-[20px] [&::-webkit-progress-bar]:bg-[#edf1f7] [&::-webkit-progress-value]:rounded-[20px] [&::-webkit-progress-value]:bg-[#2563eb] [&::-moz-progress-bar]:rounded-[20px] [&::-moz-progress-bar]:bg-[#2563eb] [&::-webkit-progress-value]:bg-[#16a34a] [&::-moz-progress-bar]:bg-[#16a34a]" value="{{ $jumlahSelesai }}" max="{{ max(1, $totalLaporan) }}">
                    {{ $jumlahSelesai }}
                </progress>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#e8edf5] bg-white shadow-[0_4px_18px_rgba(25,55,99,0.035)]">
            <div class="border-b border-[#edf1f7] px-[22px] py-[21px] [&_h2]:m-0 [&_h2]:mb-1.5 [&_h2]:text-[17px] [&_h2]:font-bold [&_p]:m-0 [&_p]:text-xs [&_p]:text-[#8190a5] max-[600px]:p-[18px]">
                <h2>Laporan berdasarkan kategori</h2>
                <p>Jumlah dihitung dari laporan instansi dalam periode yang dipilih.</p>
            </div>

            <div class="grid gap-5 px-[22px] py-5 max-[600px]:p-[17px]">
                @forelse ($kategoriStatistik as $kategori)
                    <div>
                        <div class="flex items-center justify-between gap-[15px] text-xs text-[#34445e] [&_strong]:text-[13px] [&_strong]:text-[#172b4d]">
                            <span>{{ ucfirst($kategori->kategori) }}</span>
                            <strong>{{ number_format($kategori->jumlah) }}</strong>
                        </div>
                        <progress
                            class="mt-3 block h-2 w-full appearance-none overflow-hidden rounded-[20px] border-0 bg-[#edf1f7] [&::-webkit-progress-bar]:rounded-[20px] [&::-webkit-progress-bar]:bg-[#edf1f7] [&::-webkit-progress-value]:rounded-[20px] [&::-webkit-progress-value]:bg-[#2563eb] [&::-moz-progress-bar]:rounded-[20px] [&::-moz-progress-bar]:bg-[#2563eb] [&::-webkit-progress-value]:bg-[#3979ed] [&::-moz-progress-bar]:bg-[#3979ed]"
                            value="{{ $kategori->jumlah }}"
                            max="{{ $maksimumKategori }}"
                        >
                            {{ $kategori->jumlah }}
                        </progress>
                    </div>
                @empty
                    <div class="!whitespace-normal !px-[10px] !py-6 !text-center !text-[#8794a8]">
                        <div class="mb-1.5 font-bold text-[#34445e]">Belum ada data kategori</div>
                        <div>Laporan pada periode yang dipilih akan diringkas di sini.</div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
