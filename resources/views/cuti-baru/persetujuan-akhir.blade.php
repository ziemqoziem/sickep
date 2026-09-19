<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Persetujuan Akhir') }} &mdash; {{ __('Cuti Besar & CLTN') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        modal: null,
        tahapId: null,
        nama: null,
        openModal(type, id, nama) { this.modal = type; this.tahapId = id; this.nama = nama; },
        closeModal() { this.modal = null; this.tahapId = null; this.nama = null; },
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-600 to-fuchsia-600 p-6 shadow-lg shadow-violet-600/20">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold">{{ $tahapList->total() }} pengajuan menunggu persetujuan akhir</p>
                        <p class="text-sm text-violet-100 mt-0.5">
                            Modul khusus Admin untuk <span class="font-medium text-white">Cuti Besar</span> dan
                            <span class="font-medium text-white">Cuti di Luar Tanggungan Negara</span> &mdash; hanya
                            pengajuan yang sudah disetujui Atasan Langsung <em>dan</em> Kepala Unit Kerja.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($tahapList as $tahap)
                    @php
                        $p = $tahap->cutiPengajuan;
                        $warna = $p->jenisCutiAturan->warna();
                        $tahapAtasan = $p->tahap->firstWhere('urutan', 1);
                        $tahapKepala = $p->tahap->firstWhere('urutan', 2);
                    @endphp
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm hover:shadow-md transition p-6 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $p->jenisCutiAturan->ikonPath() }}" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $p->pegawai->namaLengkap() }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->pegawai->nip }} &middot; {{ $p->pegawai->opd?->uraiunor }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $warna['soft'] }} shrink-0">
                                {{ $p->jenisCutiAturan->nama }}
                            </span>
                        </div>

                        <div class="text-sm text-slate-600">
                            {{ $p->tanggal_mulai->translatedFormat('d M Y') }}
                            @if ($p->tanggal_selesai)
                                &ndash; {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                            @endif
                            <span class="text-xs text-slate-400">({{ $p->lama_hari }} hari)</span>
                        </div>
                        <p class="text-sm text-slate-600">{{ $p->alasan }}</p>

                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500 shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="h-0.5 flex-1 max-w-8 bg-emerald-400"></span>
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500 shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="h-0.5 flex-1 max-w-8 bg-amber-300"></span>
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 shrink-0 animate-pulse">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-xs font-semibold text-amber-700 ml-1">Menunggu Final</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-xl p-3">
                            <div>
                                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Atasan Langsung</p>
                                @if ($tahapAtasan && $tahapAtasan->status === 'disetujui')
                                    <p class="text-sm text-slate-700 mt-0.5">{{ $tahapAtasan->penyetuju?->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $tahapAtasan->diputuskan_pada?->translatedFormat('d M Y H:i') }}</p>
                                @else
                                    <p class="text-sm text-slate-400 mt-0.5">Dilewati</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Kepala Unit Kerja</p>
                                <p class="text-sm text-slate-700 mt-0.5">{{ $tahapKepala?->penyetuju?->name }}</p>
                                <p class="text-xs text-slate-400">{{ $tahapKepala?->diputuskan_pada?->translatedFormat('d M Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <a href="{{ route('cuti-baru.show', $p) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                                Detail
                            </a>
                            <button type="button" @click="openModal('tolak', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition">
                                Tolak
                            </button>
                            <button type="button" @click="openModal('setuju', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-violet-700 hover:from-violet-700 hover:to-violet-800 shadow-sm shadow-violet-600/20 transition">
                                Setujui (Final)
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm px-6 py-12 text-center">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-400">Tidak ada pengajuan Cuti Besar/CLTN yang menunggu persetujuan akhir.</p>
                    </div>
                @endforelse
            </div>

            {{ $tahapList->links() }}
        </div>

        <div x-show="modal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"
                 x-show="modal !== null" x-transition.opacity></div>

            <form x-show="modal === 'setuju'" x-cloak method="POST"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan-akhir') }}/' + tahapId + '/setujui' : '#'"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-violet-600 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Setujui Pengajuan (Final)</h3>
                        <p class="text-sm text-slate-500" x-text="nama"></p>
                    </div>
                </div>
                <div>
                    <x-input-label value="Catatan (opsional)" />
                    <textarea name="catatan" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-violet-700 hover:from-violet-700 hover:to-violet-800 shadow-sm shadow-violet-600/20 transition">Setujui</button>
                </div>
            </form>

            <form x-show="modal === 'tolak'" x-cloak method="POST"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan-akhir') }}/' + tahapId + '/tolak' : '#'"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-red-600 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Tolak Pengajuan</h3>
                        <p class="text-sm text-slate-500" x-text="nama"></p>
                    </div>
                </div>
                <div>
                    <x-input-label value="Alasan Penolakan" />
                    <textarea name="catatan" rows="2" required
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-sm shadow-red-600/20 transition">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
