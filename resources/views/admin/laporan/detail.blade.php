
@extends('admin.layouts.app')

@section('title', 'Detail Laporan')

@section('content')
<div class="text-[#172b4d]">
    <a href="{{ route('admin.laporan.index') }}" class="mb-5 inline-flex items-center gap-2 text-[13px] text-[#52627a] no-underline hover:text-[#2563eb]">
        &larr; Kembali ke Data Laporan
    </a>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 [&_h1]:m-0 [&_h1]:mb-[7px] [&_h1]:text-[25px] [&_h1]:font-bold [&_h1]:tracking-[-0.6px] [&_h1]:max-[600px]:text-[21px] [&_p]:m-0 [&_p]:text-[13px] [&_p]:text-[#718096]">
        <div>
            <h1>Detail Laporan</h1>
            <p>Informasi lengkap laporan kerusakan infrastruktur.</p>
        </div>

        <span class="inline-flex items-center rounded-[20px] px-2.5 py-1.5 text-[10px] font-bold {{ match ($laporan->status) { 'menunggu' => 'bg-[#fff4dc] text-[#a56800]', 'diproses' => 'bg-[#e5efff] text-[#245ec7]', 'selesai' => 'bg-[#dcfce7] text-[#15803d]', default => 'bg-[#edf1f7] text-[#52627a]', } }}">
            {{ match ($laporan->status) {
                'menunggu' => 'Menunggu',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai',
                default => ucfirst($laporan->status ?? 'Tidak diketahui'),
            } }}
        </span>
    </div>

    <div class="grid grid-cols-[minmax(0,1.6fr)_minmax(280px,1fr)] items-start gap-5 max-[1000px]:grid-cols-1">
        <div>
            <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px]">
                <h2>Informasi Kerusakan</h2>

                <div class="mb-3 text-[22px] font-bold leading-[1.4] text-[#172b4d] max-[600px]:text-[19px]">
                    {{ $laporan->judul }}
                </div>

                <div class="text-[13px] leading-[1.8] text-[#64748b] [overflow-wrap:anywhere]">
                    {{ $laporan->deskripsi ?: 'Pelapor tidak memberikan deskripsi.' }}
                </div>

                @if ($laporan->foto)
                    <img
                        src="{{ asset('storage/' . $laporan->foto) }}"
                        alt="Foto kerusakan infrastruktur"
                        class="mt-[18px] block max-h-[420px] w-full rounded-xl bg-[#f1f5f9] object-cover"
                        data-laporan-image
                    >

                    <div class="mt-[18px] flex min-h-[180px] items-center justify-center rounded-xl bg-[#f5f8fc] text-[13px] text-[#94a3b8]" data-image-placeholder hidden>
                        Foto laporan tidak dapat ditampilkan.
                    </div>
                @else
                    <div class="mt-[18px] flex min-h-[180px] items-center justify-center rounded-xl bg-[#f5f8fc] text-[13px] text-[#94a3b8]">
                        Tidak ada foto yang dilampirkan.
                    </div>
                @endif
            </section>

            <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px]">
                <h2>Informasi Lokasi</h2>

                <div class="grid gap-[18px]">
                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Alamat</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->alamat_lokasi ?: 'Alamat belum tersedia' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Latitude</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->latitude ?? '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Longitude</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->longitude ?? '-' }}
                        </div>
                    </div>

                    @if ($laporan->latitude !== null && $laporan->longitude !== null)
                        <div class="mt-[22px] flex flex-wrap gap-2.5">
                            <a
                                href="https://www.openstreetmap.org/?mlat={{ $laporan->latitude }}&mlon={{ $laporan->longitude }}#map=17/{{ $laporan->latitude }}/{{ $laporan->longitude }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1"
                            >
                                Buka Lokasi di OpenStreetMap
                            </a>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div>
            <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px]">
                <h2>Informasi Pelapor</h2>

                <div class="grid gap-[18px]">
                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Nama</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->pelapor?->name ?? 'Pengguna tidak ditemukan' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Email</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->pelapor?->email ?? '-' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px]">
                <h2>Informasi Laporan</h2>

                <div class="grid gap-[18px]">
                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">ID Laporan</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">#{{ $laporan->id }}</div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Kategori</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->kategori ?: 'Kategori belum tersedia' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Dibuat</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->created_at
                                ? \Illuminate\Support\Carbon::parse($laporan->created_at)->format('d F Y, H:i')
                                : '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Diperbarui</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ $laporan->updated_at
                                ? \Illuminate\Support\Carbon::parse($laporan->updated_at)->format('d F Y, H:i')
                                : '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-[115px_minmax(0,1fr)] gap-3 text-xs leading-[1.6] max-[600px]:grid-cols-[95px_minmax(0,1fr)]">
                        <div class="text-[#8491a5]">Status</div>
                        <div class="font-semibold text-[#34445e] [overflow-wrap:anywhere]">
                            {{ ucfirst($laporan->status ?? 'Tidak diketahui') }}
                        </div>
                    </div>
                </div>
            </section>

            @if ($laporan->status !== 'selesai')
                <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px]">
                    <h2>Perbarui Status</h2>
                    <p class="text-[13px] leading-[1.8] text-[#64748b] [overflow-wrap:anywhere]">
                        Perubahan status dicatat pada riwayat laporan.
                    </p>

                    <form
                        action="{{ route('admin.laporan.status.update', $laporan->id) }}"
                        method="POST"
                        class="mt-[18px] grid gap-[9px] [&_label]:text-[11px] [&_label]:font-semibold [&_label]:text-[#52627a] [&_.laporan-button]:mt-[5px]"
                    >
                        @csrf
                        @method('PATCH')

                        <label for="status-baru">Status berikutnya</label>
                        <select id="status-baru" name="status" class="min-h-[42px] min-w-[145px] rounded-[10px] border border-[#dfe6f0] bg-white px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10 max-[600px]:w-full max-[600px]:min-w-0" required>
                            @if ($laporan->status === 'menunggu')
                                <option value="diproses">Diproses</option>
                            @elseif ($laporan->status === 'diproses')
                                <option value="selesai">Selesai</option>
                            @endif
                        </select>

                        <label for="catatan-status">Catatan (opsional)</label>
                        <textarea
                            id="catatan-status"
                            name="catatan"
                            class="min-h-[90px] w-full resize-y rounded-[10px] border border-[#dfe6f0] px-3 py-2.5 text-xs text-[#263750] outline-none focus:border-[#3979ed] focus:ring-[3px] focus:ring-[#3979ed]/10"
                            rows="3"
                            maxlength="1000"
                            placeholder="Tambahkan catatan perubahan status"
                        >{{ old('catatan') }}</textarea>

                        <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border-0 bg-[#2563eb] px-4 py-2.5 text-xs font-semibold text-white no-underline transition-colors hover:bg-[#1d4ed8] hover:text-white max-[600px]:flex-1">Simpan Status</button>
                    </form>
                </section>
            @endif
        </div>
    </div>

    <section class="mb-5 rounded-2xl border border-[#e8edf5] bg-white p-[22px] shadow-[0_4px_18px_rgba(25,55,99,0.035)] [&_h2]:mb-5 [&_h2]:mt-0 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-[#253957] max-[600px]:p-[18px] mt-5">
        <h2>Riwayat Status</h2>

        @if ($laporan->riwayatStatus->isNotEmpty())
            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse whitespace-nowrap [&_th]:bg-[#f8faff] [&_th]:px-[18px] [&_th]:py-[14px] [&_th]:text-left [&_th]:text-[10px] [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-[0.6px] [&_th]:text-[#718096] [&_td]:border-t [&_td]:border-[#edf1f7] [&_td]:px-[18px] [&_td]:py-4 [&_td]:align-middle [&_td]:text-xs [&_tbody_tr:hover]:bg-[#fafcff]">
                    <thead>
                        <tr>
                            <th>Status sebelumnya</th>
                            <th>Status terbaru</th>
                            <th>Waktu perubahan</th>
                            <th>Diubah oleh</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($laporan->riwayatStatus as $riwayat)
                            <tr>
                                <td>{{ ucfirst($riwayat->status_sebelumnya ?? 'Laporan dibuat') }}</td>
                                <td>{{ ucfirst($riwayat->status_baru) }}</td>
                                <td>{{ $riwayat->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $riwayat->pengguna?->name ?? 'Pengguna tidak ditemukan' }}</td>
                                <td>{{ $riwayat->catatan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-[13px] leading-[1.8] text-[#64748b] [overflow-wrap:anywhere]">Belum ada perubahan status yang tercatat.</p>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/admin-laporan.js')
@endpush
