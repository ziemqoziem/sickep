<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rekapitulasi', ['tahun' => $tahun, 'jenis_cuti' => $jenisCuti]) }}"
               class="text-slate-400 hover:text-sky-700 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Detail Rekapitulasi') }} &mdash; {{ $opdNama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 print:py-0">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 print:px-0 print:max-w-none space-y-6 print:space-y-3">

            {{-- Kop cetak: hanya tampil saat print --}}
            <div class="hidden print:flex items-center gap-4 pb-3 border-b-2 border-black">
                <img src="{{ asset('images/logo-klaten.png') }}" alt="Logo Kabupaten Klaten" class="h-16 w-auto object-contain">
                <div>
                    <p class="font-bold text-sm uppercase">Pemerintah Kabupaten Klaten</p>
                    <p class="font-bold text-lg uppercase">Detail Cuti Pegawai Tahun {{ $tahun }}</p>
                    <p class="text-xs">OPD: {{ $opdNama }} &mdash; Jenis Cuti: {{ $jenisCutiLabel }}</p>
                </div>
            </div>

            <p class="text-sm text-slate-500 print:hidden">
                Daftar pegawai dan riwayat cuti yang menyusun angka rekapitulasi untuk
                <span class="font-medium text-slate-700">{{ $opdNama }}</span>,
                tahun {{ $tahun }}, jenis cuti: {{ $jenisCutiLabel }}.
            </p>

            {{-- Kartu ringkasan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 print:hidden">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Jumlah Pengajuan</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($riwayat->total()) }}</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Pegawai Unik</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($riwayatSemua->pluck('nip_baru')->unique()->count()) }}</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-6 shadow-lg shadow-violet-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-violet-100 uppercase tracking-wider">Total Hari Cuti</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($totalHari) }}</p>
                    <p class="mt-1 text-xs text-violet-100">Hari unik, maks. 365/366 per tahun</p>
                </div>
            </div>

            <div class="flex items-center justify-between print:hidden">
                <a href="{{ route('rekapitulasi', ['tahun' => $tahun, 'jenis_cuti' => $jenisCuti]) }}"
                   class="inline-flex items-center gap-1 text-sm font-medium text-sky-600 hover:text-sky-800 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Rekapitulasi
                </a>

                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-slate-800 hover:to-slate-900 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z" />
                    </svg>
                    Cetak PDF
                </button>
            </div>

            {{-- Tabel detail (tampilan layar, dipaginasi) --}}
            <div class="print:hidden">
                @include('cuti.partials.rekapitulasi-detail-table', ['riwayat' => $riwayat])
            </div>

            <div class="print:hidden">
                {{ $riwayat->onEachSide(1)->links() }}
            </div>

            {{-- Tabel detail (versi cetak, seluruh baris tanpa paginasi) --}}
            <div class="hidden print:block">
                @include('cuti.partials.rekapitulasi-detail-table', ['riwayat' => $riwayatSemua])
            </div>

            <p class="hidden print:block text-[11px] text-slate-500 mt-2">
                Dicetak pada {{ $dicetakPada->translatedFormat('d F Y H:i') }} WIB melalui SICKEP &mdash; Sistem Informasi Cuti Kepegawaian.
            </p>
        </div>
    </div>
</x-app-layout>
