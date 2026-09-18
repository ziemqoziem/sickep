<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Ajukan Cuti') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8"
                 x-data="{
                    jenisList: @js($jenisCutiList),
                    jenisId: '{{ old('jenis_cuti_aturan_id') }}',
                    get jenis() { return this.jenisList.find(j => j.id == this.jenisId) ?? null; },
                 }">
                <form method="POST" action="{{ route('cuti-baru.ajukan.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label value="Jenis Cuti" />
                        <select name="jenis_cuti_aturan_id" x-model="jenisId" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">-- Pilih Jenis Cuti --</option>
                            @foreach ($jenisCutiList as $jc)
                                <option value="{{ $jc->id }}" {{ old('jenis_cuti_aturan_id') == $jc->id ? 'selected' : '' }}>{{ $jc->nama }}</option>
                            @endforeach
                        </select>
                        <template x-if="jenis && jenis.keterangan">
                            <p class="mt-1.5 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2" x-text="jenis.keterangan"></p>
                        </template>
                    </div>

                    <div>
                        <x-input-label value="Atasan Langsung (jenjang persetujuan pertama)" />
                        @if ($kandidatAtasan->isEmpty())
                            <p class="mt-1.5 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                                Tidak ada pejabat struktural yang tersedia di OPD Anda. Jenjang Atasan Langsung akan
                                dilewati otomatis, pengajuan langsung ke Kepala Unit Kerja.
                            </p>
                        @else
                            <select name="atasan_langsung_pegawai_id"
                                    class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                                <option value="">-- Pilih Atasan Langsung --</option>
                                @foreach ($kandidatAtasan as $k)
                                    <option value="{{ $k->id }}" {{ old('atasan_langsung_pegawai_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->namaLengkap() }} ({{ $k->jabatan ?: 'Jabatan Struktural' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Tanggal Mulai" />
                            <input type="date" name="tanggal_mulai" required value="{{ old('tanggal_mulai') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        </div>
                        <div>
                            <x-input-label value="Tanggal Selesai (opsional)" />
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        </div>
                    </div>

                    <div x-show="jenis && jenis.kode === 'MELAHIRKAN'" x-cloak>
                        <x-input-label value="Kelahiran Anak Ke-" />
                        <input type="number" name="keterangan_anak_ke" min="1" value="{{ old('keterangan_anak_ke') }}"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>

                    <div>
                        <x-input-label value="Alasan" />
                        <textarea name="alasan" rows="3" required
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">{{ old('alasan') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Alamat Selama Cuti" />
                            <input type="text" name="alamat_selama_cuti" value="{{ old('alamat_selama_cuti') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        </div>
                        <div>
                            <x-input-label value="Telepon Selama Cuti" />
                            <input type="text" name="telepon_selama_cuti" value="{{ old('telepon_selama_cuti') }}"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Lampiran Dokumen Pendukung" />
                        <input type="file" name="lampiran[]" multiple
                               class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                        <p class="mt-1 text-xs text-slate-400" x-show="jenis && jenis.perlu_dokumen">
                            Jenis cuti ini mewajibkan dokumen pendukung (mis. surat keterangan dokter).
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <a href="{{ route('cuti-baru.riwayat') }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
