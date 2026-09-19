<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Persetujuan Saya') }}
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

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-sky-600 p-6 shadow-lg shadow-indigo-600/20">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold">{{ $tahapList->total() }} pengajuan menunggu persetujuan Anda</p>
                        <p class="text-sm text-indigo-100 mt-0.5">
                            Jenjang Atasan Langsung atau Kepala Unit Kerja. Persetujuan akhir Cuti Besar &amp; CLTN ada di modul terpisah.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($tahapList as $tahap)
                    @php
                        $p = $tahap->cutiPengajuan;
                        $warna = $p->jenisCutiAturan->warna();
                    @endphp
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm hover:shadow-md transition p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br {{ $warna['from'] }} {{ $warna['to'] }} shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $p->jenisCutiAturan->ikonPath() }}" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $p->pegawai->namaLengkap() }}</p>
                            <p class="text-xs text-slate-400">{{ $p->pegawai->opd?->uraiunor }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $p->jenisCutiAturan->nama }} &middot;
                                {{ $p->tanggal_mulai->translatedFormat('d M Y') }}
                                @if ($p->tanggal_selesai)
                                    &ndash; {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                @endif
                                <span class="text-slate-400">({{ $p->lama_hari }} hari)</span>
                            </p>
                        </div>

                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 shrink-0">
                            {{ $tahap->jenjang === 'atasan_langsung' ? 'Atasan Langsung' : 'Kepala Unit Kerja' }}
                        </span>

                        <div class="flex items-center gap-1.5 shrink-0 sm:border-l sm:border-slate-100 sm:pl-4">
                            <a href="{{ route('cuti-baru.show', $p) }}" title="Detail"
                               class="p-2 rounded-lg text-slate-500 hover:bg-slate-50 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <button type="button" title="Setujui" @click="openModal('setuju', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button type="button" title="Tolak" @click="openModal('tolak', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm px-6 py-12 text-center">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-400">Tidak ada pengajuan yang menunggu persetujuan Anda.</p>
                    </div>
                @endforelse
            </div>

            {{ $tahapList->links() }}
        </div>

        {{-- Modal: Setujui / Tolak --}}
        <div x-show="modal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"
                 x-show="modal !== null" x-transition.opacity></div>

            <form x-show="modal === 'setuju'" x-cloak method="POST"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan') }}/' + tahapId + '/setujui' : '#'"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Setujui Pengajuan</h3>
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
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 shadow-sm shadow-emerald-600/20 transition">Setujui</button>
                </div>
            </form>

            <form x-show="modal === 'tolak'" x-cloak method="POST"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan') }}/' + tahapId + '/tolak' : '#'"
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
