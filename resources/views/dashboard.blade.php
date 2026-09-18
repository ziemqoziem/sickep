<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('summary-cuti') }}">
                <select name="tahun" onchange="this.form.submit()"
                        class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    @foreach ($tahunList as $t)
                        <option value="{{ $t }}" {{ $t === $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    @php
        $trenMax = max(1, collect($tren)->max('value'));
        $trenPegawaiMax = max(1, collect($trenPegawai)->max('value'));
        $jenisMax = max(1, collect($jenisCuti)->max('value'));
        $opdMax = max(1, collect($topOpd)->max('value'));
    @endphp

    <div class="py-10" x-data="{ modalSedangCuti: false, sedangCutiPage: 0, sedangCutiTotalPages: {{ max(1, (int) ceil(count($sedangCutiList) / 10)) }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-slate-500">
                Menampilkan riwayat cuti tahun <span class="font-semibold text-slate-700">{{ $tahun }}</span>
                yang telah disetujui penuh (atasan langsung &amp; pejabat berwenang).
            </p>

            {{-- KPI stat tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-5 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-sky-100 truncate">Pegawai Mengajukan Cuti</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['pegawai_unik']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-emerald-100 truncate">Ajuan Cuti Disetujui</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['total']) }}</p>
                        </div>
                    </div>
                </div>

                <button type="button" @click="modalSedangCuti = true; sedangCutiPage = 0"
                        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 p-5 shadow-lg shadow-rose-600/20 text-left hover:shadow-xl transition">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-rose-100 truncate">Sedang Cuti Hari Ini</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['sedang_cuti']) }}</p>
                        </div>
                    </div>
                    <p class="relative mt-2 text-[11px] text-rose-100 underline underline-offset-2">Lihat detail</p>
                </button>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 p-5 shadow-lg shadow-amber-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-amber-100 truncate">Jenis Cuti Terbanyak</p>
                            <p class="text-base font-bold text-white truncate">{{ $summary['jenis_terbanyak'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tren bulanan --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-semibold text-slate-800">Tren Pengajuan Cuti</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Jumlah ajuan per bulan, tahun {{ $tahun }}</p>
                        </div>
                    </div>

                    <div class="flex items-end gap-2 sm:gap-3" style="height: 180px;">
                        @foreach ($tren as $bulan)
                            @php $h = $bulan['value'] > 0 ? max(4, round($bulan['value'] / $trenMax * 160)) : 2; @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group">
                                <span class="text-[11px] font-medium text-slate-500 mb-1 opacity-0 group-hover:opacity-100 transition">
                                    {{ number_format($bulan['value']) }}
                                </span>
                                <div class="w-full max-w-[28px] rounded-t-[4px] bg-[#2a78d6] hover:bg-[#1c5cab] transition-colors"
                                     style="height: {{ $h }}px;" title="{{ $bulan['label'] }}: {{ number_format($bulan['value']) }} ajuan">
                                </div>
                                <span class="mt-2 text-[10px] text-slate-400 whitespace-nowrap">{{ $bulan['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-semibold text-slate-800">Tren Pegawai Cuti</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pegawai unik per bulan, tahun {{ $tahun }}</p>
                        </div>
                    </div>

                    <div class="flex items-end gap-2 sm:gap-3" style="height: 180px;">
                        @foreach ($trenPegawai as $bulan)
                            @php $h = $bulan['value'] > 0 ? max(4, round($bulan['value'] / $trenPegawaiMax * 160)) : 2; @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group">
                                <span class="text-[11px] font-medium text-slate-500 mb-1 opacity-0 group-hover:opacity-100 transition">
                                    {{ number_format($bulan['value']) }}
                                </span>
                                <div class="w-full max-w-[28px] rounded-t-[4px] bg-[#eb6834] hover:bg-[#c9541f] transition-colors"
                                     style="height: {{ $h }}px;" title="{{ $bulan['label'] }}: {{ number_format($bulan['value']) }} pegawai">
                                </div>
                                <span class="mt-2 text-[10px] text-slate-400 whitespace-nowrap">{{ $bulan['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Distribusi jenis cuti --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-1">Distribusi Jenis Cuti</h3>
                    <p class="text-xs text-slate-400 mb-5">Tahun {{ $tahun }}</p>

                    <div class="space-y-3">
                        @foreach ($jenisCuti as $item)
                            @php $w = $item['value'] > 0 ? max(3, round($item['value'] / $jenisMax * 100)) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-600 truncate pr-2">{{ $item['label'] }}</span>
                                    <span class="text-slate-500 font-medium shrink-0">{{ number_format($item['value']) }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-[#2a78d6]" style="width: {{ $w }}%"
                                         title="{{ $item['label'] }}: {{ number_format($item['value']) }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top OPD --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-1">OPD dengan Cuti Terbanyak</h3>
                    <p class="text-xs text-slate-400 mb-5">Top 8 OPD, tahun {{ $tahun }}</p>

                    <div class="space-y-3">
                        @foreach ($topOpd as $item)
                            @php $w = $item['value'] > 0 ? max(3, round($item['value'] / $opdMax * 100)) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-600 truncate pr-2">{{ $item['label'] }}</span>
                                    <span class="text-slate-500 font-medium shrink-0">{{ number_format($item['value']) }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-[#2a78d6]" style="width: {{ $w }}%"
                                         title="{{ $item['label'] }}: {{ number_format($item['value']) }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Riwayat cuti terbaru --}}
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Riwayat Cuti Terbaru <span class="text-slate-400 font-normal">({{ $tahun }})</span></h3>
                    <a href="{{ route('cari-cuti') }}" class="text-xs font-medium text-sky-600 hover:text-sky-700">
                        Lihat semua &rarr;
                    </a>
                </div>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($terbaru as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="text-slate-700">{{ $row->pns_pnsnam ?: '—' }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->nip_baru ?: $row->pns_pnsnip }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $row->ins_insnam ?: '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $row->keterangancuti ?: '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-500">
                                    {{ \Illuminate\Support\Carbon::parse($row->tanggalawalcltn)->translatedFormat('d M Y') }}
                                    @if ($row->tanggalakhircltn)
                                        &ndash; {{ \Illuminate\Support\Carbon::parse($row->tanggalakhircltn)->translatedFormat('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $row->lamahari }} hari</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal: Sedang Cuti Hari Ini --}}
        <div x-show="modalSedangCuti" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="modalSedangCuti = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="font-semibold text-slate-800">Pegawai Sedang Cuti Hari Ini</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ now()->translatedFormat('d M Y') }} &middot; {{ count($sedangCutiList) }} pegawai</p>
                    </div>
                    <button type="button" @click="modalSedangCuti = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($sedangCutiList as $row)
                                <tr x-show="sedangCutiPage === {{ intdiv($loop->index, 10) }}">
                                    <td class="px-6 py-3 text-sm">
                                        <p class="text-slate-700">{{ $row->pns_pnsnam ?: '—' }}</p>
                                        <p class="text-xs text-slate-400 font-mono">{{ $row->nip_baru ?: $row->pns_pnsnip }}</p>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-slate-500">{{ $row->ins_insnam ?: '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-500">{{ $row->keterangancuti ?: '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($row->tanggalawalcltn)->translatedFormat('d M Y') }}
                                        @if ($row->tanggalakhircltn)
                                            &ndash; {{ \Illuminate\Support\Carbon::parse($row->tanggalakhircltn)->translatedFormat('d M Y') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-slate-400">
                                        Tidak ada pegawai yang sedang cuti hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (count($sedangCutiList) > 10)
                    <div class="px-6 py-3 border-t border-slate-100 flex items-center justify-between shrink-0">
                        <button type="button" @click="sedangCutiPage = Math.max(0, sedangCutiPage - 1)"
                                :disabled="sedangCutiPage === 0"
                                :class="sedangCutiPage === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-50'"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 transition">
                            &larr; Sebelumnya
                        </button>
                        <p class="text-xs text-slate-400">
                            Halaman <span x-text="sedangCutiPage + 1"></span> dari {{ (int) ceil(count($sedangCutiList) / 10) }}
                        </p>
                        <button type="button" @click="sedangCutiPage = Math.min(sedangCutiTotalPages - 1, sedangCutiPage + 1)"
                                :disabled="sedangCutiPage >= sedangCutiTotalPages - 1"
                                :class="sedangCutiPage >= sedangCutiTotalPages - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-50'"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 transition">
                            Berikutnya &rarr;
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
