<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $trenMax = max(1, collect($tren)->max('value'));
        $jenisMax = max(1, collect($jenisCuti)->max('value'));
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-600 p-6 sm:p-8 shadow-lg shadow-indigo-600/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative">
                    <p class="text-sm text-indigo-100">Dashboard Kepala Unit Kerja</p>
                    <p class="text-xl sm:text-2xl font-bold text-white mt-0.5">
                        @forelse ($opdList as $opd)
                            {{ $opd->uraiunor }}{{ ! $loop->last ? ', ' : '' }}
                        @empty
                            Belum mengampu OPD manapun
                        @endforelse
                    </p>
                    <p class="text-sm text-indigo-100 mt-1">Rekap pengajuan cuti pegawai di unit kerja Anda, tahun {{ $tahun }}</p>
                </div>
            </div>

            @if ($opdList->isEmpty())
                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm px-4 py-3">
                    Akun Anda belum ditautkan ke OPD manapun. Hubungi Admin untuk mengatur cakupan OPD yang Anda ampu.
                </div>
            @endif

            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 p-5 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-sky-100 truncate">Pegawai di OPD</p>
                            <p class="text-xl font-bold text-white">{{ number_format($pegawaiCount) }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 p-5 shadow-lg shadow-indigo-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h4M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v4M9 17l3 3m0 0l3-3m-3 3V9" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-indigo-100 truncate">Total Pengajuan</p>
                            <p class="text-xl font-bold text-white">{{ $rekap['total'] }}</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('cuti-baru.persetujuan') }}"
                   class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 p-5 shadow-lg shadow-rose-600/20 hover:shadow-xl transition">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-rose-100 truncate">Menunggu Persetujuan Saya</p>
                            <p class="text-xl font-bold text-white">{{ $menungguSaya }}</p>
                        </div>
                    </div>
                    <p class="relative mt-2 text-[11px] text-rose-100 underline underline-offset-2">Proses sekarang</p>
                </a>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-emerald-100 truncate">Disetujui</p>
                            <p class="text-xl font-bold text-white">{{ $rekap['disetujui'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Tren bulanan --}}
                <div class="bg-white border border-sky-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-1">Tren Pengajuan Cuti</h3>
                    <p class="text-xs text-slate-400 mb-6">Jumlah ajuan per bulan, tahun {{ $tahun }}</p>

                    <div class="flex items-end gap-2 sm:gap-3" style="height: 180px;">
                        @foreach ($tren as $bulan)
                            @php $h = $bulan['value'] > 0 ? max(4, round($bulan['value'] / $trenMax * 160)) : 2; @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group">
                                <span class="text-[11px] font-medium text-slate-500 mb-1 opacity-0 group-hover:opacity-100 transition">
                                    {{ $bulan['value'] }}
                                </span>
                                <div class="w-full max-w-[28px] rounded-t-[4px] bg-gradient-to-t from-indigo-600 to-violet-500 hover:from-indigo-700 hover:to-violet-600 transition-colors"
                                     style="height: {{ $h }}px;" title="{{ $bulan['label'] }}: {{ $bulan['value'] }} ajuan"></div>
                                <span class="mt-2 text-[10px] text-slate-400 whitespace-nowrap">{{ $bulan['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Rekap status --}}
                <div class="bg-white border border-sky-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-1">Rekap Status</h3>
                    <p class="text-xs text-slate-400 mb-6">Tahun {{ $tahun }}</p>

                    @php
                        $rekapItems = [
                            ['label' => 'Disetujui', 'value' => $rekap['disetujui'], 'dot' => 'bg-emerald-500'],
                            ['label' => 'Menunggu', 'value' => $rekap['diajukan'], 'dot' => 'bg-amber-500'],
                            ['label' => 'Ditolak', 'value' => $rekap['ditolak'], 'dot' => 'bg-red-500'],
                            ['label' => 'Dibatalkan', 'value' => $rekap['dibatalkan'], 'dot' => 'bg-slate-400'],
                        ];
                        $rekapMax = max(1, collect($rekapItems)->max('value'));
                    @endphp
                    <div class="space-y-3">
                        @foreach ($rekapItems as $item)
                            @php $w = $item['value'] > 0 ? max(3, round($item['value'] / $rekapMax * 100)) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item['dot'] }}"></span>
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="text-slate-500 font-medium">{{ $item['value'] }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $item['dot'] }}" style="width: {{ $w }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Distribusi jenis cuti --}}
            <div class="bg-white border border-sky-100 rounded-2xl p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800 mb-1">Distribusi Jenis Cuti</h3>
                <p class="text-xs text-slate-400 mb-5">Tahun {{ $tahun }}</p>

                @if (empty($jenisCuti))
                    <p class="text-sm text-slate-400 text-center py-6">Belum ada data pengajuan.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($jenisCuti as $item)
                            @php $w = $item['value'] > 0 ? max(3, round($item['value'] / $jenisMax * 100)) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-600 truncate pr-2">{{ $item['label'] }}</span>
                                    <span class="text-slate-500 font-medium shrink-0">{{ $item['value'] }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ $w }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Pengajuan terbaru --}}
            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800">Pengajuan Terbaru di OPD Anda</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($terbaru as $row)
                        @php $warna = $row->jenisCutiAturan->warna(); $status = $row->statusWarna(); @endphp
                        <a href="{{ route('cuti-baru.show', $row) }}" class="flex items-center gap-3 px-6 py-3.5 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $row->jenisCutiAturan->ikonPath() }}" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $row->pegawai->namaLengkap() }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $row->jenisCutiAturan->nama }} &middot; {{ $row->tanggal_mulai->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $status['badge'] }} shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                {{ $row->labelStatusJenjang() }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-slate-400">Belum ada pengajuan cuti di OPD ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
