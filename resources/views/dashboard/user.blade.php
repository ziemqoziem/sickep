<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 via-sky-600 to-cyan-600 p-6 sm:p-8 shadow-lg shadow-sky-600/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative">
                    <p class="text-sm text-sky-100">Selamat datang,</p>
                    <p class="text-xl sm:text-2xl font-bold text-white mt-0.5">{{ $pegawai?->namaLengkap() ?? Auth::user()->name }}</p>
                    @if ($pegawai)
                        <p class="text-sm text-sky-100 mt-1">{{ $pegawai->nip }} &middot; {{ $pegawai->opd?->uraiunor ?: '—' }}</p>
                    @endif
                </div>
            </div>

            @unless ($pegawai)
                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm px-4 py-3">
                    Akun Anda belum ditautkan ke data pegawai, sehingga belum bisa mengajukan cuti. Hubungi Admin
                    untuk menautkan akun Anda di Master Pengguna.
                </div>
            @endunless

            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 p-5 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-sky-100 truncate">Sisa Cuti Tahunan</p>
                            <p class="text-xl font-bold text-white">{{ $sisaCutiTahunan }} <span class="text-xs font-semibold">hari</span></p>
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
                            <p class="text-xs font-medium text-indigo-100 truncate">Total Pengajuan {{ $tahun }}</p>
                            <p class="text-xl font-bold text-white">{{ $rekap['total'] }}</p>
                        </div>
                    </div>
                </div>

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

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 p-5 shadow-lg shadow-amber-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-amber-100 truncate">Menunggu Proses</p>
                            <p class="text-xl font-bold text-white">{{ $rekap['diajukan'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Rekap cuti --}}
                <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-800 mb-1">Rekap Cuti {{ $tahun }}</h3>
                    <p class="text-xs text-slate-400 mb-5">Ringkasan status seluruh pengajuan Anda tahun ini</p>

                    <div class="space-y-3">
                        @php
                            $rekapItems = [
                                ['label' => 'Disetujui', 'value' => $rekap['disetujui'], 'dot' => 'bg-emerald-500'],
                                ['label' => 'Menunggu', 'value' => $rekap['diajukan'], 'dot' => 'bg-amber-500'],
                                ['label' => 'Ditolak', 'value' => $rekap['ditolak'], 'dot' => 'bg-red-500'],
                                ['label' => 'Dibatalkan', 'value' => $rekap['dibatalkan'], 'dot' => 'bg-slate-400'],
                            ];
                            $rekapMax = max(1, collect($rekapItems)->max('value'));
                        @endphp
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

                {{-- Riwayat terbaru --}}
                <div class="lg:col-span-2 bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-800">Riwayat Cuti Terbaru</h3>
                        <a href="{{ route('cuti-baru.riwayat') }}" class="text-xs font-medium text-sky-600 hover:text-sky-700">
                            Lihat semua &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($riwayatTerbaru as $row)
                            @php $warna = $row->jenisCutiAturan->warna(); $status = $row->statusWarna(); @endphp
                            <a href="{{ route('cuti-baru.show', $row) }}" class="flex items-center gap-3 px-6 py-3.5 hover:bg-slate-50 transition">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $row->jenisCutiAturan->ikonPath() }}" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $row->jenisCutiAturan->nama }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $row->tanggal_mulai->translatedFormat('d M Y') }}
                                        @if ($row->tanggal_selesai)
                                            &ndash; {{ $row->tanggal_selesai->translatedFormat('d M Y') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $status['badge'] }} shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                    {{ $row->labelStatusJenjang() }}
                                </span>
                            </a>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-slate-400">Belum ada pengajuan cuti.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <a href="{{ route('cuti-baru.ajukan') }}"
               class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 via-slate-700 to-slate-800 p-6 shadow-lg shadow-slate-600/20 flex items-center justify-between transition hover:shadow-xl">
                <div class="absolute -right-6 -bottom-8 w-28 h-28 rounded-full bg-white/5 group-hover:bg-white/10 transition"></div>
                <div class="relative">
                    <p class="text-sm font-semibold text-slate-100">Ingin mengajukan cuti baru?</p>
                    <p class="text-xs text-slate-300 mt-0.5">Isi form pengajuan cuti dalam hitungan menit</p>
                </div>
                <span class="relative inline-flex items-center gap-2 rounded-lg bg-white/15 group-hover:bg-white/25 px-4 py-2 text-sm font-semibold text-white transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Ajukan Cuti
                </span>
            </a>
        </div>
    </div>
</x-app-layout>
