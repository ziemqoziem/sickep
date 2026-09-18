<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-sky-700 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Detail Pengajuan') }} &mdash; {{ $pengajuan->nomor_pengajuan }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-lg font-semibold text-slate-800">{{ $pengajuan->jenisCutiAturan->nama }}</p>
                        <p class="text-sm text-slate-500">{{ $pengajuan->pegawai->namaLengkap() }} &middot; {{ $pengajuan->pegawai->nip }}</p>
                    </div>
                    @php
                        $color = match (true) {
                            $pengajuan->status === 'disetujui' => 'bg-emerald-50 text-emerald-700',
                            $pengajuan->status === 'ditolak' => 'bg-red-50 text-red-700',
                            $pengajuan->status === 'dibatalkan' => 'bg-slate-100 text-slate-500',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                        {{ $pengajuan->labelStatusJenjang() }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-400 uppercase tracking-wider">Tanggal</dt>
                        <dd class="mt-0.5 text-slate-700">
                            {{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }}
                            @if ($pengajuan->tanggal_selesai)
                                &ndash; {{ $pengajuan->tanggal_selesai->translatedFormat('d M Y') }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400 uppercase tracking-wider">Lama</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $pengajuan->lama_hari }} hari</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400 uppercase tracking-wider">OPD</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $pengajuan->pegawai->opd?->uraiunor ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400 uppercase tracking-wider">Atasan Langsung</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $pengajuan->atasanLangsungPegawai?->namaLengkap() ?: '—' }}</dd>
                    </div>
                    @if ($pengajuan->nomor_sk)
                        <div>
                            <dt class="text-xs text-slate-400 uppercase tracking-wider">Nomor SK</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $pengajuan->nomor_sk }}</dd>
                        </div>
                    @endif
                </dl>

                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Alasan</p>
                    <p class="text-sm text-slate-700">{{ $pengajuan->alasan }}</p>
                </div>

                @if ($pengajuan->lampiran->isNotEmpty())
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Lampiran</p>
                        <ul class="space-y-1">
                            @foreach ($pengajuan->lampiran as $file)
                                <li>
                                    <a href="{{ Storage::url($file->path_file) }}" target="_blank"
                                       class="text-sm text-sky-600 hover:text-sky-800 transition">
                                        {{ $file->nama_dokumen }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($pengajuan->status === 'disetujui' && $pengajuan->pdf_path)
                    <a href="{{ Storage::url($pengajuan->pdf_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-sky-700 hover:to-sky-800 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                        </svg>
                        Unduh PDF
                    </a>
                @endif
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8">
                <p class="text-sm font-semibold text-slate-700 mb-4">Jejak Persetujuan</p>
                <ol class="space-y-4">
                    @foreach ($pengajuan->tahap as $tahap)
                        @php
                            $jenjangLabel = match ($tahap->jenjang) {
                                'atasan_langsung' => 'Atasan Langsung',
                                'kepala_unit_kerja' => 'Kepala Unit Kerja',
                                'admin' => 'Admin (Persetujuan Akhir)',
                                default => $tahap->jenjang,
                            };
                            $statusColor = match ($tahap->status) {
                                'disetujui' => 'bg-emerald-50 text-emerald-700',
                                'ditolak' => 'bg-red-50 text-red-700',
                                'dilewati' => 'bg-slate-100 text-slate-500',
                                default => 'bg-amber-50 text-amber-700',
                            };
                        @endphp
                        <li class="flex items-start justify-between gap-3 border-b border-slate-50 pb-4 last:border-0 last:pb-0">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $jenjangLabel }}</p>
                                @if ($tahap->penyetuju)
                                    <p class="text-xs text-slate-400">{{ $tahap->penyetuju->name }} &middot; {{ $tahap->diputuskan_pada?->translatedFormat('d M Y H:i') }}</p>
                                @endif
                                @if ($tahap->catatan)
                                    <p class="text-xs text-slate-500 mt-1 italic">&ldquo;{{ $tahap->catatan }}&rdquo;</p>
                                @endif
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }} capitalize">
                                {{ $tahap->status }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
