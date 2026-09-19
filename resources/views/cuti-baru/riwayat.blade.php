<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Riwayat Cuti Saya') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('sync_error'))
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    {{ session('sync_error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 via-sky-600 to-cyan-600 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/10"></div>
                    <div class="absolute right-8 bottom-2 w-14 h-14 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Sisa Cuti Tahunan {{ now()->year }}</p>
                            <p class="mt-1 text-3xl font-bold text-white">{{ $sisaCutiTahunan }} <span class="text-base font-semibold text-sky-100">hari</span></p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('cuti-baru.ajukan') }}"
                   class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 via-slate-700 to-slate-800 p-6 shadow-lg shadow-slate-600/20 flex items-center justify-between transition hover:shadow-xl">
                    <div class="absolute -right-6 -bottom-8 w-28 h-28 rounded-full bg-white/5 group-hover:bg-white/10 transition"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Ajukan Cuti Baru</p>
                        <p class="mt-1 text-sm text-slate-100">Isi form pengajuan cuti</p>
                    </div>
                    <span class="relative inline-flex items-center gap-2 rounded-lg bg-white/15 group-hover:bg-white/25 px-4 py-2 text-sm font-semibold text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajukan
                    </span>
                </a>
            </div>

            @if (! $pegawai)
                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm px-4 py-3">
                    Akun Anda belum ditautkan ke data pegawai, sehingga belum bisa mengajukan cuti. Hubungi Admin
                    untuk menautkan akun Anda di Master Pengguna.
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($riwayat as $row)
                    @php
                        $warna = $row->jenisCutiAturan->warna();
                        $status = $row->statusWarna();
                    @endphp
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm hover:shadow-md transition p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $row->jenisCutiAturan->ikonPath() }}" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-800">{{ $row->jenisCutiAturan->nama }}</p>
                                <span class="text-xs font-mono text-slate-400">{{ $row->nomor_pengajuan }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $row->tanggal_mulai->translatedFormat('d M Y') }}
                                @if ($row->tanggal_selesai)
                                    &ndash; {{ $row->tanggal_selesai->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $status['badge'] }} shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                            {{ $row->labelStatusJenjang() }}
                        </span>

                        <div class="flex items-center gap-3 shrink-0 sm:border-l sm:border-slate-100 sm:pl-4">
                            <a href="{{ route('cuti-baru.show', $row) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition">
                                Detail
                            </a>
                            @if ($row->status === 'diajukan')
                                <form method="POST" action="{{ route('cuti-baru.batalkan', $row) }}"
                                      onsubmit="return confirm('Batalkan pengajuan {{ $row->nomor_pengajuan }}?');">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 transition">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm px-6 py-12 text-center">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm text-slate-400">Belum ada pengajuan cuti.</p>
                    </div>
                @endforelse
            </div>

            {{ $riwayat->links() }}
        </div>
    </div>
</x-app-layout>
