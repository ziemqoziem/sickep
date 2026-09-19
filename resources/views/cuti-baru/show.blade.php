<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-sky-700 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Detail Pengajuan') }}
            </h2>
        </div>
    </x-slot>

    @php
        $warna = $pengajuan->jenisCutiAturan->warna();
        $status = $pengajuan->statusWarna();
    @endphp

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} p-6 sm:p-8 shadow-lg shadow-sky-600/10">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pengajuan->jenisCutiAturan->ikonPath() }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-mono text-white/70">{{ $pengajuan->nomor_pengajuan }}</p>
                            <p class="text-lg sm:text-xl font-bold text-white mt-0.5">{{ $pengajuan->jenisCutiAturan->nama }}</p>
                            <p class="text-sm text-white/80 mt-0.5">{{ $pengajuan->pegawai->namaLengkap() }} &middot; {{ $pengajuan->pegawai->nip }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/15 text-white">
                        <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }} ring-2 ring-white/40"></span>
                        {{ $pengajuan->labelStatusJenjang() }}
                    </span>
                </div>
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 text-sm">
                    <div>
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Tanggal
                        </dt>
                        <dd class="mt-1 text-slate-700 font-medium">
                            {{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }}
                            @if ($pengajuan->tanggal_selesai)
                                &ndash; {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Lama
                        </dt>
                        <dd class="mt-1 text-slate-700 font-medium">{{ $pengajuan->lama_hari }} hari</dd>
                    </div>
                    <div>
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10" /></svg>
                            OPD
                        </dt>
                        <dd class="mt-1 text-slate-700 font-medium">{{ $pengajuan->pegawai->opd?->uraiunor ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Atasan Langsung
                        </dt>
                        <dd class="mt-1 text-slate-700 font-medium">{{ $pengajuan->atasanLangsungPegawai?->namaLengkap() ?: '—' }}</dd>
                    </div>
                    @if ($pengajuan->nomor_sk)
                        <div>
                            <dt class="flex items-center gap-1.5 text-xs text-slate-400 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Nomor SK
                            </dt>
                            <dd class="mt-1 text-slate-700 font-medium">{{ $pengajuan->nomor_sk }}</dd>
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1.5">Alasan</p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $pengajuan->alasan }}</p>
                </div>

                @if ($pengajuan->lampiran->isNotEmpty())
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Lampiran</p>
                        <ul class="space-y-1.5">
                            @foreach ($pengajuan->lampiran as $file)
                                <li>
                                    <a href="{{ Storage::url($file->path_file) }}" target="_blank"
                                       class="inline-flex items-center gap-2 text-sm text-sky-600 hover:text-sky-800 transition">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        {{ $file->nama_dokumen }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($pengajuan->status === 'disetujui' && $pengajuan->pdf_path)
                    <div class="border-t border-slate-100 pt-5">
                        <a href="{{ Storage::url($pengajuan->pdf_path) }}" target="_blank"
                           class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            Unduh PDF
                        </a>
                    </div>
                @endif
            </div>

            {{-- Stepper Jejak Persetujuan --}}
            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8">
                <p class="text-sm font-semibold text-slate-700 mb-6">Jejak Persetujuan</p>

                <ol class="relative">
                    @foreach ($pengajuan->tahap as $i => $tahap)
                        @php
                            $jenjangLabel = match ($tahap->jenjang) {
                                'atasan_langsung' => 'Atasan Langsung',
                                'kepala_unit_kerja' => 'Kepala Unit Kerja',
                                'admin' => 'Admin (Persetujuan Akhir)',
                                default => $tahap->jenjang,
                            };
                            $node = match ($tahap->status) {
                                'disetujui' => ['ring' => 'bg-emerald-500 ring-emerald-100', 'line' => 'bg-emerald-400', 'text' => 'text-emerald-700'],
                                'ditolak' => ['ring' => 'bg-red-500 ring-red-100', 'line' => 'bg-slate-200', 'text' => 'text-red-700'],
                                'dilewati' => ['ring' => 'bg-slate-300 ring-slate-100', 'line' => 'bg-slate-200', 'text' => 'text-slate-400'],
                                default => ['ring' => 'bg-amber-500 ring-amber-100 animate-pulse', 'line' => 'bg-slate-200', 'text' => 'text-amber-700'],
                            };
                            $isLast = $i === $pengajuan->tahap->count() - 1;
                        @endphp
                        <li class="relative flex gap-4 pb-8 last:pb-0">
                            @if (! $isLast)
                                <span class="absolute left-[15px] top-8 bottom-0 w-0.5 {{ $node['line'] }}"></span>
                            @endif

                            <span class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full {{ $node['ring'] }} ring-4 shrink-0">
                                @if ($tahap->status === 'disetujui')
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                @elseif ($tahap->status === 'ditolak')
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                @elseif ($tahap->status === 'dilewati')
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                @else
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </span>

                            <div class="flex-1 flex items-start justify-between gap-3 pt-0.5">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $jenjangLabel }}</p>
                                    @if ($tahap->penyetuju)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $tahap->penyetuju->name }} &middot; {{ $tahap->diputuskan_pada?->translatedFormat('d M Y H:i') }}</p>
                                    @endif
                                    @if ($tahap->catatan)
                                        <p class="text-xs text-slate-500 mt-1.5 italic bg-slate-50 rounded-lg px-3 py-1.5 inline-block">&ldquo;{{ $tahap->catatan }}&rdquo;</p>
                                    @endif
                                </div>
                                <span class="shrink-0 text-xs font-semibold {{ $node['text'] }} capitalize">
                                    {{ $tahap->status }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
