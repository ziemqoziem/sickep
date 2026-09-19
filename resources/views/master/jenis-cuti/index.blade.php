<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Aturan Cuti') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        formOpen: false,
        mode: 'create',
        target: null,
        form: {
            kode: '', nama: '', syarat_masa_kerja_bulan: '', jatah_hari: '', carry_over_hari: '',
            maks_hari: '', perlu_dokumen: false, butuh_persetujuan_admin: false, keterangan: '', aktif: true,
        },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = {
                kode: '', nama: '', syarat_masa_kerja_bulan: '', jatah_hari: '', carry_over_hari: '',
                maks_hari: '', perlu_dokumen: false, butuh_persetujuan_admin: false, keterangan: '', aktif: true,
            };
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                kode: row.kode, nama: row.nama,
                syarat_masa_kerja_bulan: row.syarat_masa_kerja_bulan ?? '',
                jatah_hari: row.jatah_hari ?? '', carry_over_hari: row.carry_over_hari ?? '',
                maks_hari: row.maks_hari ?? '', perlu_dokumen: !!row.perlu_dokumen,
                butuh_persetujuan_admin: !!row.butuh_persetujuan_admin,
                keterangan: row.keterangan ?? '', aktif: !!row.aktif,
            };
            this.formOpen = true;
        },
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-slate-500">
                Aturan dasar tiap jenis cuti (syarat masa kerja, jatah/plafon hari, dokumen wajib, dan jenjang
                persetujuan) untuk fitur pengajuan cuti. Lihat <span class="font-medium">ketentuan-cuti.md</span>
                untuk rujukan lengkapnya.
            </p>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('master.jenis-cuti') }}" class="flex gap-3 flex-1 max-w-md">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama..."
                           class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                        Cari
                    </button>
                </form>

                <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Aturan
                </button>
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Syarat / Plafon</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenjang</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($aturan as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">{{ $row->nama }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->kode }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    @if ($row->syarat_masa_kerja_bulan)
                                        <p>Masa kerja &ge; {{ $row->syarat_masa_kerja_bulan }} bulan</p>
                                    @endif
                                    @if ($row->jatah_hari)
                                        <p>Jatah {{ $row->jatah_hari }} hari/tahun @if ($row->carry_over_hari)(carry-over maks. {{ $row->carry_over_hari }})@endif</p>
                                    @endif
                                    @if ($row->maks_hari)
                                        <p>Maks. {{ number_format($row->maks_hari) }} hari</p>
                                    @endif
                                    @if ($row->perlu_dokumen)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 mt-1">Wajib dokumen</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($row->butuh_persetujuan_admin)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700">
                                            Atasan &rarr; OPD &rarr; Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700">
                                            Atasan &rarr; OPD
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($row->aktif)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button type="button" title="Edit" @click="openEdit(@js($row))"
                                            class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">
                                    Tidak ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $aturan->links() }}
        </div>

        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="formOpen = false"></div>

            <form method="POST"
                  :action="mode === 'create' ? '{{ route('master.jenis-cuti.store') }}' : '{{ url('master/jenis-cuti') }}/' + (target ? target.id : '')"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">

                <div>
                    <h3 class="font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Aturan Cuti' : 'Edit Aturan Cuti'"></h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Kode" />
                        <input type="text" name="kode" x-model="form.kode" required maxlength="20"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono uppercase">
                    </div>
                    <div>
                        <x-input-label value="Nama" />
                        <input type="text" name="nama" x-model="form.nama" required maxlength="100"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Syarat Masa Kerja (bulan)" />
                        <input type="number" name="syarat_masa_kerja_bulan" x-model="form.syarat_masa_kerja_bulan" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Maks. Hari / Periode" />
                        <input type="number" name="maks_hari" x-model="form.maks_hari" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Jatah Hari / Tahun" />
                        <input type="number" name="jatah_hari" x-model="form.jatah_hari" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Carry-over (hari)" />
                        <input type="number" name="carry_over_hari" x-model="form.carry_over_hari" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Keterangan" />
                        <textarea name="keterangan" x-model="form.keterangan" rows="3"
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                    </div>
                    <div class="col-span-2 flex flex-wrap gap-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="perlu_dokumen" x-model="form.perlu_dokumen" value="1"
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-slate-600">Wajib dokumen pendukung</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="butuh_persetujuan_admin" x-model="form.butuh_persetujuan_admin" value="1"
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-slate-600">Perlu persetujuan akhir Admin</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="aktif" x-model="form.aktif" value="1"
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-slate-600">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="formOpen = false"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
