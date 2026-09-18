<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Rekapitulasi Cuti Tahunan') }}
        </h2>
    </x-slot>

    <div class="py-10 print:py-0">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 print:px-0 print:max-w-none space-y-6 print:space-y-3">

            {{-- Kop cetak: hanya tampil saat print --}}
            <div class="hidden print:flex items-center gap-4 pb-3 border-b-2 border-black">
                <img src="{{ asset('images/logo-klaten.png') }}" alt="Logo Kabupaten Klaten" class="h-16 w-auto object-contain">
                <div>
                    <p class="font-bold text-sm uppercase">Pemerintah Kabupaten Klaten</p>
                    <p class="font-bold text-lg uppercase">Rekapitulasi Cuti Pegawai Tahun {{ $tahun }}</p>
                    <p class="text-xs">Jenis Cuti: {{ $jenisCutiLabel }}</p>
                </div>
            </div>

            <p class="text-sm text-slate-500 print:hidden">
                Rekap jumlah pengajuan dan total hari cuti per OPD yang telah disetujui penuh (atasan langsung &amp; pejabat berwenang), berdasarkan tahun dan jenis cuti.
            </p>

            {{-- Kartu ringkasan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 print:hidden">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Jumlah Pengajuan</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($grand->jumlah_pengajuan) }}</p>
                    <p class="mt-1 text-xs text-sky-100">Tahun {{ $tahun }} &mdash; {{ $jenisCutiLabel }}</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Pegawai Mengajukan</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($grand->jumlah_pegawai) }}</p>
                    <p class="mt-1 text-xs text-emerald-100">Pegawai unik</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-6 shadow-lg shadow-violet-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-violet-100 uppercase tracking-wider">Total Hari Cuti</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($grand->total_hari) }}</p>
                    <p class="mt-1 text-xs text-violet-100">Hari unik, maks. 365/366 per tahun</p>
                </div>
            </div>

            {{-- Filter & aksi cetak --}}
            <form method="GET" action="{{ route('rekapitulasi') }}" class="flex flex-wrap items-end gap-3 print:hidden">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Tahun</label>
                    <select name="tahun" class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}" {{ $tahun === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Cuti</label>
                    <select name="jenis_cuti" class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm min-w-[220px]">
                        <option value="">Semua Jenis Cuti</option>
                        @foreach ($jenisCutiList as $jc)
                            <option value="{{ $jc->kodecuti }}" {{ $jenisCuti === $jc->kodecuti ? 'selected' : '' }}>
                                {{ $jc->keterangancuti }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                    Tampilkan
                </button>

                <button type="button" onclick="window.print()"
                        class="ms-auto inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-slate-800 hover:to-slate-900 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z" />
                    </svg>
                    Cetak PDF
                </button>
            </form>

            {{-- Tabel rekap (tampilan layar, dipaginasi) --}}
            <div class="print:hidden">
                @include('cuti.partials.rekapitulasi-table', ['rows' => $rows])
            </div>

            <p class="text-xs text-slate-400 print:hidden">
                * Total Hari Cuti dihitung dari hari kalender unik (satu tanggal dihitung sekali walau dipakai beberapa pegawai/pengajuan sekaligus) -- maksimal 365 hari per tahun (366 di tahun kabisat), bukan akumulasi jumlah.
            </p>

            <div class="print:hidden">
                {{ $rows->onEachSide(1)->links() }}
            </div>

            {{-- Tabel rekap (versi cetak, seluruh baris tanpa paginasi) --}}
            <div class="hidden print:block">
                @include('cuti.partials.rekapitulasi-table', ['rows' => $allRows])
            </div>

            <p class="hidden print:block text-[11px] text-slate-500 mt-2">
                Dicetak pada {{ $dicetakPada->translatedFormat('d F Y H:i') }} WIB melalui SICKEP &mdash; Sistem Informasi Cuti Kepegawaian.
            </p>
        </div>
    </div>
</x-app-layout>
