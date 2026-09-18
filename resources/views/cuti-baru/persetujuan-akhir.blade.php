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

            <p class="text-sm text-slate-500">
                Modul khusus Admin untuk persetujuan akhir <span class="font-medium">Cuti Besar</span> dan
                <span class="font-medium">Cuti di Luar Tanggungan Negara</span> &mdash; hanya menampilkan pengajuan
                yang sudah disetujui Atasan Langsung <em>dan</em> Kepala Unit Kerja.
            </p>

            <div class="space-y-4">
                @forelse ($tahapList as $tahap)
                    @php
                        $p = $tahap->cutiPengajuan;
                        $tahapAtasan = $p->tahap->firstWhere('urutan', 1);
                        $tahapKepala = $p->tahap->firstWhere('urutan', 2);
                    @endphp
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $p->pegawai->namaLengkap() }}</p>
                                <p class="text-xs text-slate-400">{{ $p->pegawai->nip }} &middot; {{ $p->pegawai->opd?->uraiunor }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700">
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

                        <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-lg p-3">
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

                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('cuti-baru.show', $p) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                                Detail
                            </a>
                            <button type="button" @click="openModal('tolak', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition">
                                Tolak
                            </button>
                            <button type="button" @click="openModal('setuju', {{ $tahap->id }}, @js($p->pegawai->namaLengkap()))"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 transition">
                                Setujui (Final)
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-sky-100 rounded-2xl px-6 py-6 text-center text-sm text-slate-400">
                        Tidak ada pengajuan Cuti Besar/CLTN yang menunggu persetujuan akhir.
                    </div>
                @endforelse
            </div>

            {{ $tahapList->links() }}
        </div>

        {{-- Modal: Setujui / Tolak --}}
        <div x-show="modal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="closeModal()"></div>

            <form x-show="modal === 'setuju'" x-cloak method="POST"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan-akhir') }}/' + tahapId + '/setujui' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="font-semibold text-slate-800">Setujui Pengajuan (Final)</h3>
                    <p class="text-sm text-slate-500 mt-1" x-text="nama"></p>
                </div>
                <div>
                    <x-input-label value="Catatan (opsional)" />
                    <textarea name="catatan" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">Setujui</button>
                </div>
            </form>

            <form x-show="modal === 'tolak'" x-cloak method="POST"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan-akhir') }}/' + tahapId + '/tolak' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="font-semibold text-slate-800">Tolak Pengajuan</h3>
                    <p class="text-sm text-slate-500 mt-1" x-text="nama"></p>
                </div>
                <div>
                    <x-input-label value="Alasan Penolakan" />
                    <textarea name="catatan" rows="2" required
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
