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

            <p class="text-sm text-slate-500">
                Daftar pengajuan cuti yang menunggu persetujuan Anda pada jenjang Atasan Langsung atau Kepala Unit
                Kerja. Persetujuan akhir Cuti Besar &amp; CLTN ada di modul terpisah.
            </p>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenjang</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tahapList as $tahap)
                            @php $p = $tahap->cutiPengajuan; @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">{{ $p->pegawai->namaLengkap() }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->pegawai->opd?->uraiunor }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-700">{{ $p->jenisCutiAturan->nama }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ $p->tanggal_mulai->translatedFormat('d M Y') }}
                                    @if ($p->tanggal_selesai)
                                        &ndash; {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                    @endif
                                    <span class="text-xs text-slate-400">({{ $p->lama_hari }} hari)</span>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700">
                                        {{ $tahap->jenjang === 'atasan_langsung' ? 'Atasan Langsung' : 'Kepala Unit Kerja' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">
                                    Tidak ada pengajuan yang menunggu persetujuan Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tahapList->links() }}
        </div>

        {{-- Modal: Setujui / Tolak --}}
        <div x-show="modal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="closeModal()"></div>

            <form x-show="modal === 'setuju'" x-cloak method="POST"
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan') }}/' + tahapId + '/setujui' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="font-semibold text-slate-800">Setujui Pengajuan</h3>
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
                  :action="tahapId ? '{{ url('cuti-baru/persetujuan') }}/' + tahapId + '/tolak' : '#'"
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
