<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        formOpen: false,
        deleteOpen: false,
        mode: 'create',
        target: null,
        opdList: @js($opdList),
        pegawaiQuery: '', pegawaiResults: [], pegawaiOpen: false, pegawaiLoading: false, pegawaiTimer: null,
        opdQuery: '', opdOpen: false,
        form: { name: '', email: '', password: '', role: 'user', pegawai_id: '', opd_ids: [] },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = { name: '', email: '', password: '', role: 'user', pegawai_id: '', opd_ids: [] };
            this.pegawaiQuery = '';
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                name: row.name, email: row.email, password: '', role: row.role,
                pegawai_id: row.pegawai_id ?? '',
                opd_ids: (row.opd_diampu ?? []).map(o => o.id),
            };
            this.pegawaiQuery = row.pegawai ? row.pegawai.nama : '';
            this.formOpen = true;
        },
        openDelete(row) { this.target = row; this.deleteOpen = true; },
        searchPegawai() {
            clearTimeout(this.pegawaiTimer);
            if (this.pegawaiQuery.trim().length < 2) { this.pegawaiResults = []; this.pegawaiOpen = false; return; }
            this.pegawaiTimer = setTimeout(() => {
                this.pegawaiLoading = true;
                fetch('{{ route('master.pegawai.search') }}?q=' + encodeURIComponent(this.pegawaiQuery))
                    .then(r => r.json())
                    .then(data => { this.pegawaiResults = data; this.pegawaiOpen = true; this.pegawaiLoading = false; });
            }, 300);
        },
        pickPegawai(item) {
            this.form.pegawai_id = item.id;
            this.pegawaiQuery = item.nama;
            this.pegawaiOpen = false;
        },
        clearPegawai() {
            this.form.pegawai_id = '';
            this.pegawaiQuery = '';
        },
        opdResults() {
            const q = this.opdQuery.trim().toLowerCase();
            const list = q === '' ? this.opdList : this.opdList.filter(o => o.uraiunor.toLowerCase().includes(q));
            return list.filter(o => ! this.form.opd_ids.includes(o.id)).slice(0, 30);
        },
        addOpd(opd) { this.form.opd_ids.push(opd.id); this.opdQuery = ''; this.opdOpen = false; },
        removeOpd(id) { this.form.opd_ids = this.form.opd_ids.filter(x => x !== id); },
        opdName(id) { const o = this.opdList.find(x => x.id === id); return o ? o.uraiunor : id; },
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                <form method="GET" action="{{ route('master.pengguna') }}" class="flex gap-3 flex-1 max-w-md">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau email..."
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
                    Tambah Pengguna
                </button>
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Peran</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Pegawai / OPD</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengguna as $row)
                            @php
                                $rowPayload = [
                                    'id' => $row->id,
                                    'name' => $row->name,
                                    'email' => $row->email,
                                    'role' => $row->role,
                                    'pegawai_id' => $row->pegawai_id,
                                    'pegawai' => $row->pegawai ? ['id' => $row->pegawai->id, 'nama' => $row->pegawai->namaLengkap(), 'nip' => $row->pegawai->nip] : null,
                                    'opd_diampu' => $row->opdDiampu->map(fn ($o) => ['id' => $o->id])->values(),
                                ];
                            @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $row->photoUrl() }}" alt="{{ $row->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-sky-50">
                                        <div>
                                            <p class="font-medium text-slate-800">
                                                {{ $row->name }}
                                                @if ($row->id === Auth::id())
                                                    <span class="ml-1 text-[11px] text-slate-400">(Anda)</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-slate-400">{{ $row->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                        'bg-sky-50 text-sky-700' => $row->role === 'admin',
                                        'bg-violet-50 text-violet-700' => $row->role === 'opd',
                                        'bg-slate-100 text-slate-600' => $row->role === 'user',
                                    ])>
                                        {{ $row->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    @if ($row->pegawai)
                                        <p>{{ $row->pegawai->namaLengkap() }}</p>
                                        <p class="text-xs text-slate-400 font-mono">{{ $row->pegawai->nip }}</p>
                                    @endif
                                    @if ($row->role === 'opd')
                                        <p class="text-xs text-violet-600 mt-0.5">{{ $row->opdDiampu->pluck('uraiunor')->implode(', ') ?: 'Belum ada OPD diampu' }}</p>
                                    @endif
                                    @if (! $row->pegawai && $row->role !== 'opd')
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" title="Edit"
                                                @click="openEdit(@js($rowPayload))"
                                                class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        @if ($row->id !== Auth::id())
                                            <button type="button" title="Hapus" @click="openDelete(@js($row))"
                                                    class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-sm text-slate-400">
                                    Tidak ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $pengguna->links() }}
        </div>

        {{-- Modal: Create/Edit --}}
        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="formOpen = false"></div>

            <form method="POST"
                  :action="mode === 'create' ? '{{ route('master.pengguna.store') }}' : '{{ url('master/pengguna') }}/' + (target ? target.id : '')"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="pegawai_id" :value="form.pegawai_id">
                <template x-for="id in form.opd_ids" :key="id">
                    <input type="hidden" name="opd_ids[]" :value="id">
                </template>

                <div>
                    <h3 class="font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Pengguna' : 'Edit Pengguna'"></h3>
                </div>

                <div>
                    <x-input-label value="Nama" />
                    <input type="text" name="name" x-model="form.name" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div>
                    <x-input-label value="Email" />
                    <input type="email" name="email" x-model="form.email" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div>
                    <x-input-label value="Password" />
                    <input type="password" name="password" x-model="form.password" autocomplete="new-password"
                           :placeholder="mode === 'edit' ? 'Kosongkan jika tidak diubah' : ''"
                           :required="mode === 'create'"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div>
                    <x-input-label value="Peran" />
                    <select name="role" x-model="form.role" required
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <option value="user">User</option>
                        <option value="opd">OPD (Kepala Unit Kerja)</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="relative">
                    <x-input-label value="Pegawai Terhubung (opsional)" />
                    <input type="text" x-model="pegawaiQuery"
                           @input="searchPegawai()" @focus="pegawaiOpen = pegawaiResults.length > 0" @click.outside="pegawaiOpen = false"
                           autocomplete="off" placeholder="Ketik nama atau NIP pegawai..."
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <button type="button" x-show="form.pegawai_id" @click="clearPegawai()"
                            class="absolute right-2 top-8 text-xs text-slate-400 hover:text-red-600">&times;</button>
                    <p class="mt-1 text-xs text-slate-400">Menautkan akun ke data kepegawaian (diperlukan agar bisa mengajukan/menyetujui cuti).</p>

                    <div x-show="pegawaiOpen" x-cloak
                         class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="item in pegawaiResults" :key="item.id">
                            <button type="button" @click="pickPegawai(item)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50 border-b border-slate-50 last:border-0">
                                <p class="font-medium text-slate-700" x-text="item.nama"></p>
                                <p class="text-xs text-slate-400" x-text="item.nip"></p>
                            </button>
                        </template>
                        <p x-show="!pegawaiLoading && pegawaiResults.length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
                    </div>
                </div>

                <div x-show="form.role === 'opd'" x-cloak class="relative">
                    <x-input-label value="OPD yang Diampu" />
                    <input type="text" x-model="opdQuery" @focus="opdOpen = true" @click.outside="opdOpen = false"
                           autocomplete="off" placeholder="Ketik untuk mencari & menambah OPD..."
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">

                    <div class="mt-2 flex flex-wrap gap-1.5" x-show="form.opd_ids.length > 0">
                        <template x-for="id in form.opd_ids" :key="id">
                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 text-violet-700 text-xs font-medium pl-2.5 pr-1 py-0.5">
                                <span x-text="opdName(id)"></span>
                                <button type="button" @click="removeOpd(id)" class="hover:text-violet-900">&times;</button>
                            </span>
                        </template>
                    </div>

                    <div x-show="opdOpen" x-cloak
                         class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="opd in opdResults()" :key="opd.id">
                            <button type="button" @click="addOpd(opd)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-sky-50 border-b border-slate-50 last:border-0"
                                    x-text="opd.uraiunor"></button>
                        </template>
                        <p x-show="opdResults().length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
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

            <form method="POST" :action="target ? '{{ url('master/pengguna') }}/' + target.id : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
                @csrf
                @method('DELETE')
                <input type="hidden" name="q" value="{{ $q }}">
                <div>
                    <h3 class="font-semibold text-slate-800">Hapus Pengguna</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Yakin ingin menghapus akun <span class="font-medium" x-text="target?.name"></span>?
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
