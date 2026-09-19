<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Ajukan Cuti') }}
        </h2>
    </x-slot>

    @php
        $jenisJs = $jenisCutiList->map(fn ($jc) => [
            'id' => $jc->id,
            'kode' => $jc->kode,
            'nama' => $jc->nama,
            'keterangan' => $jc->keterangan,
            'perlu_dokumen' => (bool) $jc->perlu_dokumen,
            'warna' => $jc->warna(),
            'ikon' => $jc->ikonPath(),
        ]);
    @endphp

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8"
             x-data="{
                jenisList: @js($jenisJs),
                jenisId: '{{ old('jenis_cuti_aturan_id') }}',
                get jenis() { return this.jenisList.find(j => j.id == this.jenisId) ?? null; },
             }">

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1 mb-6">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- Live preview --}}
                <div class="lg:col-span-1 lg:sticky lg:top-6 order-first lg:order-last">
                    <div class="relative overflow-hidden rounded-2xl p-6 shadow-lg transition-all duration-300 bg-gradient-to-br"
                         :class="jenis ? [jenis.warna.from, jenis.warna.to] : ['from-slate-400', 'to-slate-500']">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                        <div class="absolute -left-6 -bottom-8 w-24 h-24 rounded-full bg-white/10"></div>

                        <div class="relative">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          :d="jenis ? jenis.ikon : 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2'" />
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-white/70 uppercase tracking-wider">Jenis Cuti Dipilih</p>
                            <p class="text-lg font-bold text-white mt-1" x-text="jenis ? jenis.nama : 'Belum dipilih'"></p>
                            <template x-if="jenis && jenis.keterangan">
                                <p class="text-xs text-white/80 mt-2 leading-relaxed" x-text="jenis.keterangan"></p>
                            </template>
                            <template x-if="jenis && jenis.perlu_dokumen">
                                <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-white/15 rounded-full px-3 py-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                    Wajib dokumen pendukung
                                </p>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-sky-100 bg-white p-5 text-xs text-slate-500 leading-relaxed">
                        Lengkapi seluruh isian pada formulir untuk mengajukan cuti. Pengajuan akan diproses secara
                        berjenjang mulai dari Atasan Langsung.
                    </div>
                </div>

                {{-- Form --}}
                <div class="lg:col-span-2 space-y-5">
                    <form method="POST" action="{{ route('cuti-baru.ajukan.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        {{-- Section: Jenis & Atasan --}}
                        <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-7 space-y-5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-sky-600 text-white text-xs font-bold">1</span>
                                <p class="text-sm font-semibold text-slate-700">Jenis Cuti &amp; Atasan Langsung</p>
                            </div>

                            <div>
                                <x-input-label value="Jenis Cuti" />
                                <select name="jenis_cuti_aturan_id" x-model="jenisId" required
                                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                                    <option value="">-- Pilih Jenis Cuti --</option>
                                    @foreach ($jenisCutiList as $jc)
                                        <option value="{{ $jc->id }}" {{ old('jenis_cuti_aturan_id') == $jc->id ? 'selected' : '' }}>{{ $jc->nama }}</option>
                                    @endforeach
                                </select>
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
                        </div>

                        {{-- Section: Tanggal --}}
                        <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-7 space-y-5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-sky-600 text-white text-xs font-bold">2</span>
                                <p class="text-sm font-semibold text-slate-700">Rentang Waktu</p>
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
                        </div>

                        {{-- Section: Detail --}}
                        <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-7 space-y-5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-sky-600 text-white text-xs font-bold">3</span>
                                <p class="text-sm font-semibold text-slate-700">Detail Tambahan</p>
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
                                <p class="mt-1.5 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 inline-block" x-show="jenis && jenis.perlu_dokumen" x-cloak>
                                    Jenis cuti ini mewajibkan dokumen pendukung (mis. surat keterangan dokter).
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-1 pb-2">
                            <a href="{{ route('cuti-baru.riwayat') }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 hover:shadow-md transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
