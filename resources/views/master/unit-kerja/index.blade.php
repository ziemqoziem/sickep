<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Unit Kerja') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        formOpen: false,
        deleteOpen: false,
        mode: 'create',
        target: null,
        form: { idunor: '', uraiunor: '', akronim: '', nama_jabatan: '', nama_kepala: '', nip_kepala: '', pangkat_kepala: '', golongan_kepala: '', status_jabatan: '' },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = { idunor: '', uraiunor: '', akronim: '', nama_jabatan: '', nama_kepala: '', nip_kepala: '', pangkat_kepala: '', golongan_kepala: '', status_jabatan: '' };
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                idunor: row.idunor, uraiunor: row.uraiunor, akronim: row.akronim ?? '',
                nama_jabatan: row.nama_jabatan ?? '', nama_kepala: row.nama_kepala ?? '',
                nip_kepala: row.nip_kepala ?? '', pangkat_kepala: row.pangkat_kepala ?? '',
                golongan_kepala: row.golongan_kepala ?? '', status_jabatan: row.status_jabatan ?? '',
            };
            this.formOpen = true;
        },
        openDelete(row) { this.target = row; this.deleteOpen = true; },
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

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('master.unit-kerja') }}" class="flex flex-wrap gap-3 flex-1">
                    <div class="flex gap-3 flex-1 max-w-md">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode, nama, atau akronim..."
                               class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                            Cari
                        </button>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-500">
                        Tampilkan
                        <select name="per_page" onchange="this.form.submit()"
                                class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm py-1.5">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                        / halaman
                    </label>
                </form>

                <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Unit Kerja
                </button>
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Kode / Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Kepala Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status Jabatan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($unitKerja as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">
                                        {{ $row->uraiunor }}
                                        @if ($row->akronim)
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-sky-50 text-sky-700">{{ $row->akronim }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->idunor }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <p class="text-slate-700">{{ $row->nama_kepala ?: '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $row->nama_jabatan ?: ($row->nip_kepala ?: '') }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($row->status_jabatan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                            {{ $row->status_jabatan }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ number_format($row->pegawai_count) }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" title="Edit" @click="openEdit(@js($row))"
                                                class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" title="Hapus" @click="openDelete(@js($row))"
                                                class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

            {{ $unitKerja->links() }}
        </div>

        {{-- Modal: Create/Edit --}}
        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="formOpen = false"></div>

            <form method="POST"
                  :action="mode === 'create' ? '{{ route('master.unit-kerja.store') }}' : '{{ url('master/unit-kerja') }}/' + (target ? target.id : '')"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div>
                    <h3 class="font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Unit Kerja' : 'Edit Unit Kerja'"></h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <x-input-label value="Kode Unit (idunor)" />
                        <input type="text" name="idunor" x-model="form.idunor" required maxlength="30"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono">
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Nama Unit Kerja" />
                        <input type="text" name="uraiunor" x-model="form.uraiunor" required maxlength="255"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Akronim" />
                        <input type="text" name="akronim" x-model="form.akronim" maxlength="50"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Status Jabatan" />
                        <select name="status_jabatan" x-model="form.status_jabatan"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="Definitif">Definitif</option>
                            <option value="Pelaksana Tugas (Plt)">Pelaksana Tugas (Plt)</option>
                            <option value="Pelaksana Harian (Plh)">Pelaksana Harian (Plh)</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Nama Jabatan Kepala" />
                        <input type="text" name="nama_jabatan" x-model="form.nama_jabatan" maxlength="200"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="col-span-2 relative"
                         x-data="{
                            results: [], open: false, loading: false, timer: null,
                            search() {
                                clearTimeout(this.timer);
                                if (form.nama_kepala.trim().length < 2) { this.results = []; this.open = false; return; }
                                this.timer = setTimeout(() => {
                                    this.loading = true;
                                    fetch('{{ route('master.pegawai.search') }}?q=' + encodeURIComponent(form.nama_kepala))
                                        .then(r => r.json())
                                        .then(data => { this.results = data; this.open = true; this.loading = false; });
                                }, 300);
                            },
                            pick(item) {
                                form.nama_kepala = item.nama;
                                form.nip_kepala = item.nip;
                                form.pangkat_kepala = item.pangkat ?? '';
                                form.golongan_kepala = item.golongan ?? '';
                                this.open = false;
                            },
                         }">
                        <x-input-label value="Nama Kepala Unit" />
                        <input type="text" name="nama_kepala" x-model="form.nama_kepala"
                               @input="search()" @focus="open = results.length > 0" @click.outside="open = false"
                               autocomplete="off" maxlength="150" placeholder="Ketik nama atau NIP pegawai..."
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <p class="mt-1 text-xs text-slate-400">Cari dari data Master Pegawai; NIP, pangkat, dan golongan terisi otomatis.</p>

                        <div x-show="open" x-cloak
                             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                            <template x-for="item in results" :key="item.nip">
                                <button type="button" @click="pick(item)"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50 border-b border-slate-50 last:border-0">
                                    <p class="font-medium text-slate-700" x-text="item.nama"></p>
                                    <p class="text-xs text-slate-400" x-text="item.nip + (item.jabatan ? ' · ' + item.jabatan : '')"></p>
                                </button>
                            </template>
                            <p x-show="!loading && results.length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
                        </div>
                    </div>
                    <div>
                        <x-input-label value="NIP Kepala" />
                        <input type="text" name="nip_kepala" x-model="form.nip_kepala" maxlength="18"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono">
                    </div>
                    <div>
                        <x-input-label value="Pangkat Kepala" />
                        <input type="text" name="pangkat_kepala" x-model="form.pangkat_kepala" maxlength="50"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="col-span-2">
                        <x-input-label value="Golongan Kepala" />
                        <input type="text" name="golongan_kepala" x-model="form.golongan_kepala" maxlength="10"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
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

        {{-- Modal: Delete confirm --}}
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="deleteOpen = false"></div>

            <form method="POST" :action="target ? '{{ url('master/unit-kerja') }}/' + target.id : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
                @csrf
                @method('DELETE')
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div>
                    <h3 class="font-semibold text-slate-800">Hapus Unit Kerja</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Yakin ingin menghapus <span class="font-medium" x-text="target?.uraiunor"></span>?
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="deleteOpen = false"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
