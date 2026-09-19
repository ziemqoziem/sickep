<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Master Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        formOpen: false,
        actionModal: null,
        mode: 'create',
        target: null,
        opdList: @js($opdList),
        opdQuery: '', opdOpen: false,
        mutasiOpdId: '', mutasiOpdQuery: '', mutasiOpdOpen: false,
        opdResults(query) {
            const q = query.trim().toLowerCase();
            const list = q === '' ? this.opdList : this.opdList.filter(o => o.uraiunor.toLowerCase().includes(q));
            return list.slice(0, 30);
        },
        pickOpd(opd) { this.form.opd_id = opd.id; this.opdQuery = opd.uraiunor; this.opdOpen = false; },
        pickMutasiOpd(opd) { this.mutasiOpdId = opd.id; this.mutasiOpdQuery = opd.uraiunor; this.mutasiOpdOpen = false; },
        golonganPangkat: {
            'I/a': 'Juru Muda', 'I/b': 'Juru Muda Tingkat I', 'I/c': 'Juru', 'I/d': 'Juru Tingkat I',
            'II/a': 'Pengatur Muda', 'II/b': 'Pengatur Muda Tingkat I', 'II/c': 'Pengatur', 'II/d': 'Pengatur Tingkat I',
            'III/a': 'Penata Muda', 'III/b': 'Penata Muda Tingkat I', 'III/c': 'Penata', 'III/d': 'Penata Tingkat I',
            'IV/a': 'Pembina', 'IV/b': 'Pembina Tingkat I', 'IV/c': 'Pembina Utama Muda',
            'IV/d': 'Pembina Utama Madya', 'IV/e': 'Pembina Utama',
        },
        form: {
            nip: '', nama: '', gelar_depan: '', gelar_belakang: '', status_kepegawaian: 'PNS',
            golongan: '', pangkat: '', pendidikan: '', jabatan: '', jenis_jabatan: '',
            unit_kerja: '', opd_id: '', status_aktif: 'Aktif',
        },
        pilihGolongan() {
            this.form.pangkat = this.golonganPangkat[this.form.golongan] ?? '';
        },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = {
                nip: '', nama: '', gelar_depan: '', gelar_belakang: '', status_kepegawaian: 'PNS',
                golongan: '', pangkat: '', pendidikan: '', jabatan: '', jenis_jabatan: '',
                unit_kerja: '', opd_id: '', status_aktif: 'Aktif',
            };
            this.opdQuery = '';
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                nip: row.nip, nama: row.nama, gelar_depan: row.gelar_depan ?? '', gelar_belakang: row.gelar_belakang ?? '',
                status_kepegawaian: row.status_kepegawaian, golongan: row.golongan ?? '', pangkat: row.pangkat ?? '',
                pendidikan: row.pendidikan ?? '', jabatan: row.jabatan ?? '', jenis_jabatan: row.jenis_jabatan ?? '',
                unit_kerja: row.unit_kerja ?? '', opd_id: row.opd_id, status_aktif: row.status_aktif,
            };
            this.opdQuery = row.opd ? row.opd.uraiunor : '';
            this.formOpen = true;
        },
        openAction(type, row) {
            this.actionModal = type;
            this.target = row;
            if (type === 'mutasi') {
                this.mutasiOpdId = '';
                this.mutasiOpdQuery = '';
            }
        },
    }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Total Pegawai</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['aktif']) }}</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-500 to-slate-600 p-6 shadow-lg shadow-slate-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-slate-200 uppercase tracking-wider">Tidak Aktif</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['tidak_aktif']) }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('master.pegawai') }}" class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="flex gap-3 flex-1 max-w-md">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, NIP, atau unit kerja..."
                               class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                            Cari
                        </button>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status[]" value="Aktif" onchange="this.form.submit()"
                                   {{ in_array('Aktif', $status) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            Aktif
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status[]" value="Tidak Aktif" onchange="this.form.submit()"
                                   {{ in_array('Tidak Aktif', $status) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            Tidak Aktif
                        </label>
                    </div>
                </form>

                <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pegawai
                </button>
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Nama / NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jabatan / Unit Kerja</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">OPD</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pegawai as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">{{ $row->namaLengkap() }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->nip }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <p class="text-slate-700">{{ $row->jabatan ?: '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $row->unit_kerja ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ $row->opd?->uraiunor ?: '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($row->status_aktif === 'Aktif')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" title="Edit" @click="openEdit(@js($row))"
                                                class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        @if ($row->status_aktif === 'Aktif')
                                            <button type="button" title="Non Aktifkan Pegawai"
                                                    @click="openAction('nonaktifkan', @js($row))"
                                                    class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v9" />
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button" title="Aktifkan Kembali"
                                                    @click="openAction('aktifkan', @js($row))"
                                                    class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button type="button" title="Mutasi"
                                                @click="openAction('mutasi', @js($row))"
                                                class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        </button>
                                    </div>
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

            {{ $pegawai->links() }}
        </div>

        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="formOpen = false"></div>

            <form method="POST"
                  :action="mode === 'create' ? '{{ route('master.pegawai.store') }}' : '{{ url('master/pegawai') }}/' + (target ? target.id : '')"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">
                @foreach ($status as $s)
                    <input type="hidden" name="status[]" value="{{ $s }}">
                @endforeach

                <div>
                    <h3 class="font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Pegawai' : 'Edit Pegawai'"></h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="NIP" />
                        <input type="text" name="nip" x-model="form.nip" required maxlength="18"
                               :readonly="mode === 'edit'"
                               :class="mode === 'edit' ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : ''"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono">
                    </div>
                    <div>
                        <x-input-label value="Nama" />
                        <input type="text" name="nama" x-model="form.nama" required maxlength="150"
                               :readonly="mode === 'edit'"
                               :class="mode === 'edit' ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : ''"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Gelar Depan" />
                        <input type="text" name="gelar_depan" x-model="form.gelar_depan" maxlength="30"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Gelar Belakang" />
                        <input type="text" name="gelar_belakang" x-model="form.gelar_belakang" maxlength="100"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Status Kepegawaian" />
                        <select name="status_kepegawaian" x-model="form.status_kepegawaian" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="PNS">PNS</option>
                            <option value="PPPK">PPPK</option>
                            <option value="PPPK Paruh Waktu">PPPK Paruh Waktu</option>
                            <option value="Calon PNS">Calon PNS</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Status Aktif" />
                        <select name="status_aktif" x-model="form.status_aktif" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Golongan" />
                        <select name="golongan" x-model="form.golongan" @change="pilihGolongan()"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">-- Pilih --</option>
                            <template x-for="g in Object.keys(golonganPangkat)" :key="g">
                                <option :value="g" x-text="g"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Pangkat" />
                        <input type="text" name="pangkat" x-model="form.pangkat" maxlength="100"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <p class="mt-1 text-xs text-slate-400">Terisi otomatis dari golongan; bisa disunting bila perlu.</p>
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Pendidikan" />
                        <input type="text" name="pendidikan" x-model="form.pendidikan" maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Jabatan" />
                        <input type="text" name="jabatan" x-model="form.jabatan" maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Jenis Jabatan" />
                        <select name="jenis_jabatan" x-model="form.jenis_jabatan"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="Jabatan Struktural">Jabatan Struktural</option>
                            <option value="Jabatan Fungsional">Jabatan Fungsional</option>
                            <option value="Jabatan Pelaksana">Jabatan Pelaksana</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Unit Kerja (nama bebas)" />
                        <input type="text" name="unit_kerja" x-model="form.unit_kerja" maxlength="255"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="col-span-2 relative">
                        <x-input-label value="OPD Induk" />
                        <input type="text" x-model="opdQuery" required
                               @input="opdOpen = true; form.opd_id = ''"
                               @focus="opdOpen = true" @click.outside="opdOpen = false"
                               autocomplete="off" placeholder="Ketik untuk mencari OPD..."
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <input type="hidden" name="opd_id" :value="form.opd_id">

                        <div x-show="opdOpen" x-cloak
                             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                            <template x-for="opd in opdResults(opdQuery)" :key="opd.id">
                                <button type="button" @click="pickOpd(opd)"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50 border-b border-slate-50 last:border-0"
                                        x-text="opd.uraiunor"></button>
                            </template>
                            <p x-show="opdResults(opdQuery).length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
                        </div>
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

        <div x-show="actionModal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="actionModal = null"></div>

            <form x-show="actionModal === 'nonaktifkan'" x-cloak method="POST"
                  :action="target ? '{{ url('master/pegawai') }}/' + target.id + '/nonaktifkan' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <input type="hidden" name="q" value="{{ $q }}">
                @foreach ($status as $s)
                    <input type="hidden" name="status[]" value="{{ $s }}">
                @endforeach
                <div>
                    <h3 class="font-semibold text-slate-800">Non Aktifkan Pegawai</h3>
                    <p class="text-sm text-slate-500 mt-1" x-text="target?.nama"></p>
                </div>
                <div>
                    <x-input-label value="Alasan" />
                    <textarea name="alasan" rows="3" required
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div>
                    <x-input-label value="Tanggal Efektif" />
                    <input type="date" name="tanggal_efektif" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = null"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                        Non Aktifkan
                    </button>
                </div>
            </form>

            <form x-show="actionModal === 'aktifkan'" x-cloak method="POST"
                  :action="target ? '{{ url('master/pegawai') }}/' + target.id + '/aktifkan' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <input type="hidden" name="q" value="{{ $q }}">
                @foreach ($status as $s)
                    <input type="hidden" name="status[]" value="{{ $s }}">
                @endforeach
                <div>
                    <h3 class="font-semibold text-slate-800">Aktifkan Kembali</h3>
                    <p class="text-sm text-slate-500 mt-1" x-text="target?.nama"></p>
                </div>
                <div>
                    <x-input-label value="Catatan (opsional)" />
                    <textarea name="alasan" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div>
                    <x-input-label value="Tanggal Efektif" />
                    <input type="date" name="tanggal_efektif" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = null"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                        Aktifkan
                    </button>
                </div>
            </form>

            <form x-show="actionModal === 'mutasi'" x-cloak method="POST"
                  :action="target ? '{{ url('master/pegawai') }}/' + target.id + '/mutasi' : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <input type="hidden" name="q" value="{{ $q }}">
                @foreach ($status as $s)
                    <input type="hidden" name="status[]" value="{{ $s }}">
                @endforeach
                <div>
                    <h3 class="font-semibold text-slate-800">Mutasi Pegawai</h3>
                    <p class="text-sm text-slate-500 mt-1" x-text="target?.nama"></p>
                </div>
                <div class="relative">
                    <x-input-label value="OPD Tujuan" />
                    <input type="text" x-model="mutasiOpdQuery" required
                           @input="mutasiOpdOpen = true; mutasiOpdId = ''"
                           @focus="mutasiOpdOpen = true" @click.outside="mutasiOpdOpen = false"
                           autocomplete="off" placeholder="Ketik untuk mencari OPD..."
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <input type="hidden" name="opd_tujuan_id" :value="mutasiOpdId">

                    <div x-show="mutasiOpdOpen" x-cloak
                         class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="opd in opdResults(mutasiOpdQuery)" :key="opd.id">
                            <button type="button" @click="pickMutasiOpd(opd)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50 border-b border-slate-50 last:border-0"
                                    x-text="opd.uraiunor"></button>
                        </template>
                        <p x-show="opdResults(mutasiOpdQuery).length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
                    </div>
                </div>
                <div>
                    <x-input-label value="Unit Kerja Tujuan (opsional)" />
                    <x-text-input name="unit_kerja_tujuan" type="text" class="mt-1 block w-full" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Nomor SK" />
                        <x-text-input name="nomor_sk" type="text" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label value="Tanggal SK" />
                        <input type="date" name="tanggal_sk"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                </div>
                <div>
                    <x-input-label value="Tanggal Efektif" />
                    <input type="date" name="tanggal_efektif" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div>
                    <x-input-label value="Keterangan (opsional)" />
                    <textarea name="keterangan" rows="2"
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="actionModal = null"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 transition">
                        Simpan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
