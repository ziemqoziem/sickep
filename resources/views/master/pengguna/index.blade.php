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
        form: { name: '', username: '', email: '', no_hp: '', password: '', role: 'user', pegawai_id: '', opd_ids: [] },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = { name: '', username: '', email: '', no_hp: '', password: '', role: 'user', pegawai_id: '', opd_ids: [] };
            this.pegawaiQuery = '';
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                name: row.name, username: row.username, email: row.email ?? '', no_hp: row.no_hp ?? '', password: '', role: row.role,
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

        genOpen: false,
        genSubmitting: false,
        genType: 'user',
        genMode: 'single',
        genPegawaiQuery: '', genPegawaiResults: [], genPegawaiOpen: false, genPegawaiLoading: false, genPegawaiTimer: null,
        genPegawaiPicked: null,
        genOpdId: '',
        genConfirm: false,
        openGenerate() {
            this.genType = 'user';
            this.genMode = 'single';
            this.genPegawaiQuery = '';
            this.genPegawaiResults = [];
            this.genPegawaiPicked = null;
            this.genOpdId = '';
            this.genConfirm = false;
            this.genSubmitting = false;
            this.genOpen = true;
        },
        setGenType(type) {
            this.genType = type;
            this.genMode = 'single';
            this.genOpdId = '';
            this.genConfirm = false;
        },
        setGenMode(mode) {
            this.genMode = mode;
            this.genOpdId = '';
            this.genConfirm = false;
        },
        searchGenPegawai() {
            clearTimeout(this.genPegawaiTimer);
            this.genPegawaiPicked = null;
            if (this.genPegawaiQuery.trim().length < 2) { this.genPegawaiResults = []; this.genPegawaiOpen = false; return; }
            this.genPegawaiTimer = setTimeout(() => {
                this.genPegawaiLoading = true;
                fetch('{{ route('master.pegawai.search') }}?q=' + encodeURIComponent(this.genPegawaiQuery))
                    .then(r => r.json())
                    .then(data => { this.genPegawaiResults = data; this.genPegawaiOpen = true; this.genPegawaiLoading = false; });
            }, 300);
        },
        pickGenPegawai(item) {
            this.genPegawaiPicked = item;
            this.genPegawaiQuery = item.nama;
            this.genPegawaiOpen = false;
        },
        genOpd() {
            return this.opdList.find(o => o.id == this.genOpdId) ?? null;
        },
        genUsernamePreview() {
            if (this.genType === 'user' && this.genMode === 'single') {
                return this.genPegawaiPicked ? this.genPegawaiPicked.nip : null;
            }
            if (this.genType === 'user') {
                return 'NIP masing-masing pegawai';
            }
            if (this.genMode === 'single') {
                const opd = this.genOpd();
                return opd && opd.idunor ? opd.idunor.toLowerCase() : null;
            }
            return 'idunor masing-masing OPD';
        },
        genValid() {
            if (this.genType === 'user' && this.genMode === 'single') {
                return this.genPegawaiPicked && ! this.genPegawaiPicked.has_user;
            }
            if (this.genType === 'user') {
                return !! this.genOpdId && this.genConfirm;
            }
            if (this.genMode === 'single') {
                return !! this.genOpdId;
            }
            return this.genConfirm;
        },
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

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <form method="GET" action="{{ route('master.pengguna') }}" class="flex flex-wrap gap-3 flex-1">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, username, email, atau NIP..."
                           @input.debounce.500ms="$el.form.requestSubmit()"
                           class="flex-1 min-w-[180px] rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">

                    <select name="role" onchange="this.form.submit()"
                            class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <option value="">Semua Peran</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="opd" {{ $role === 'opd' ? 'selected' : '' }}>OPD</option>
                        <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User</option>
                    </select>

                    <select name="per_page" onchange="this.form.submit()"
                            class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        @foreach ($perPageOptions as $opt)
                            <option value="{{ $opt }}" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }} / halaman</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                        Cari
                    </button>
                </form>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="openGenerate()"
                            class="inline-flex items-center gap-2 rounded-lg border border-sky-200 text-sky-700 px-4 py-2 text-sm font-semibold hover:bg-sky-50 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Generate Pengguna
                    </button>
                    <button type="button" @click="openCreate()"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Pengguna
                    </button>
                </div>
            </div>

            <div class="flex justify-center sm:justify-end">
                {{ $pengguna->links() }}
            </div>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Nama OPD</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Peran</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengguna as $row)
                            @php
                                $rowPayload = [
                                    'id' => $row->id,
                                    'name' => $row->name,
                                    'username' => $row->username,
                                    'email' => $row->email,
                                    'no_hp' => $row->no_hp,
                                    'role' => $row->role,
                                    'pegawai_id' => $row->pegawai_id,
                                    'pegawai' => $row->pegawai ? ['id' => $row->pegawai->id, 'nama' => $row->pegawai->namaLengkap(), 'nip' => $row->pegawai->nip] : null,
                                    'opd_diampu' => $row->opdDiampu->map(fn ($o) => ['id' => $o->id])->values(),
                                ];
                                $warna = $row->roleWarna();
                            @endphp
                            <tr @class(['opacity-60' => ! $row->aktif])>
                                <td class="px-6 py-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $row->photoUrl() }}" alt="{{ $row->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-sky-50 shrink-0">
                                        <div class="min-w-0">
                                            @if ($row->role === 'user')
                                                @if ($row->pegawai)
                                                    <p class="font-medium text-slate-800 font-mono truncate">
                                                        {{ $row->pegawai->nip }}
                                                        @if ($row->id === Auth::id())
                                                            <span class="ml-1 text-[11px] text-slate-400 font-sans">(Anda)</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-slate-500 truncate">{{ $row->pegawai->namaLengkap() }}</p>
                                                    <p class="text-xs text-slate-400 truncate">{{ $row->pegawai->jabatan ?: '—' }}</p>
                                                @else
                                                    <p class="font-medium text-slate-800 font-mono truncate">
                                                        {{ $row->username }}
                                                        @if ($row->id === Auth::id())
                                                            <span class="ml-1 text-[11px] text-slate-400 font-sans">(Anda)</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-slate-400 truncate">{{ $row->name }}</p>
                                                @endif
                                            @elseif ($row->role === 'opd')
                                                <p class="font-medium text-slate-800 font-mono truncate">
                                                    {{ $row->username }}
                                                    @if ($row->id === Auth::id())
                                                        <span class="ml-1 text-[11px] text-slate-400 font-sans">(Anda)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-slate-500 truncate">{{ $row->opdDiampu->pluck('uraiunor')->implode(', ') ?: 'Belum ada OPD diampu' }}</p>
                                            @else
                                                <p class="font-medium text-slate-800 font-mono truncate">
                                                    {{ $row->username }}
                                                    @if ($row->id === Auth::id())
                                                        <span class="ml-1 text-[11px] text-slate-400 font-sans">(Anda)</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    @if ($row->pegawai)
                                        {{ $row->pegawai->opd?->uraiunor ?: '—' }}
                                    @elseif ($row->role === 'opd')
                                        {{ $row->opdDiampu->pluck('uraiunor')->implode(', ') ?: 'Belum ada OPD diampu' }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold text-white bg-gradient-to-r {{ $warna['from'] }} {{ $warna['to'] }} shadow-sm">
                                        {{ $warna['label'] }}
                                    </span>
                                    @unless ($row->aktif)
                                        <span class="ml-1 inline-flex items-center gap-1 text-[11px] font-medium text-red-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Nonaktif
                                        </span>
                                    @endunless
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
                                            @if ($row->aktif)
                                                <form method="POST" action="{{ route('master.pengguna.nonaktifkan', $row) }}"
                                                      onsubmit="return confirm('Nonaktifkan akun ' + {{ Illuminate\Support\Js::from($row->name) }} + '? Akun ini tidak akan bisa login lagi sampai diaktifkan kembali.');">
                                                    @csrf
                                                    <input type="hidden" name="q" value="{{ $q }}">
                                                    <input type="hidden" name="role" value="{{ $role }}">
                                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                                    <button type="submit" title="Nonaktifkan"
                                                            class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('master.pengguna.aktifkan', $row) }}">
                                                    @csrf
                                                    <input type="hidden" name="q" value="{{ $q }}">
                                                    <input type="hidden" name="role" value="{{ $role }}">
                                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                                    <button type="submit" title="Aktifkan"
                                                            class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
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
        </div>

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
                    <x-input-label value="Username (untuk login)" />
                    <input type="text" name="username" x-model="form.username" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Email (opsional)" />
                        <input type="email" name="email" x-model="form.email" maxlength="255"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="No. HP (opsional)" />
                        <input type="text" name="no_hp" x-model="form.no_hp" maxlength="30"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
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

        <div x-show="genOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="genOpen = false"></div>

            <form method="POST" action="{{ route('master.pengguna.generate') }}" @submit="genSubmitting = true"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <input type="hidden" name="type" :value="genType">
                <input type="hidden" name="mode" :value="genMode">
                <input type="hidden" name="pegawai_id" :value="genPegawaiPicked ? genPegawaiPicked.id : ''">
                <input type="hidden" name="opd_id" :value="genOpdId">
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-sky-600 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Generate Pengguna</h3>
                        <p class="text-xs text-slate-500">Username teks murni (NIP/kode OPD), password default</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="setGenType('user')"
                            :class="genType === 'user' ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-2 text-sm font-semibold transition">
                        Pegawai (User)
                    </button>
                    <button type="button" @click="setGenType('opd')"
                            :class="genType === 'opd' ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-2 text-sm font-semibold transition">
                        OPD (Admin)
                    </button>
                </div>

                <div x-show="genType === 'user'" x-cloak class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="setGenMode('single')"
                            :class="genMode === 'single' ? 'bg-sky-50 text-sky-700 border-sky-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-1.5 font-semibold transition">
                        Per Pegawai
                    </button>
                    <button type="button" @click="setGenMode('all')"
                            :class="genMode === 'all' ? 'bg-sky-50 text-sky-700 border-sky-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-1.5 font-semibold transition">
                        Per OPD (Semua Pegawai)
                    </button>
                </div>

                <div x-show="genType === 'opd'" x-cloak class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="setGenMode('single')"
                            :class="genMode === 'single' ? 'bg-violet-50 text-violet-700 border-violet-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-1.5 font-semibold transition">
                        Satu OPD
                    </button>
                    <button type="button" @click="setGenMode('all')"
                            :class="genMode === 'all' ? 'bg-violet-50 text-violet-700 border-violet-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                            class="rounded-lg border px-3 py-1.5 font-semibold transition">
                        Semua OPD
                    </button>
                </div>

                <div x-show="genType === 'user' && genMode === 'single'" x-cloak class="relative">
                    <x-input-label value="Cari Pegawai" />
                    <input type="text" x-model="genPegawaiQuery"
                           @input="searchGenPegawai()" @focus="genPegawaiOpen = genPegawaiResults.length > 0" @click.outside="genPegawaiOpen = false"
                           autocomplete="off" placeholder="Ketik nama atau NIP pegawai..."
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">

                    <div x-show="genPegawaiOpen" x-cloak
                         class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="item in genPegawaiResults" :key="item.id">
                            <button type="button" @click="pickGenPegawai(item)" :disabled="item.has_user"
                                    :class="item.has_user ? 'opacity-50 cursor-not-allowed' : 'hover:bg-sky-50'"
                                    class="w-full text-left px-3 py-2 text-sm border-b border-slate-50 last:border-0">
                                <p class="font-medium text-slate-700" x-text="item.nama"></p>
                                <p class="text-xs text-slate-400">
                                    <span x-text="item.nip"></span>
                                    <span x-show="item.has_user" class="text-amber-600 ml-1">&middot; sudah punya akun</span>
                                </p>
                            </button>
                        </template>
                        <p x-show="!genPegawaiLoading && genPegawaiResults.length === 0" class="px-3 py-2 text-xs text-slate-400">Tidak ditemukan.</p>
                    </div>

                    <template x-if="genPegawaiPicked && genPegawaiPicked.has_user">
                        <p class="mt-1.5 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                            Pegawai ini sudah memiliki akun pengguna.
                        </p>
                    </template>
                </div>

                <div x-show="genType === 'user' && genMode === 'all'" x-cloak>
                    <x-input-label value="Pilih OPD" />
                    <select x-model="genOpdId" @change="genConfirm = false"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        <option value="">-- Pilih OPD --</option>
                        <template x-for="opd in opdList" :key="opd.id">
                            <option :value="opd.id" x-text="opd.uraiunor"></option>
                        </template>
                    </select>

                    <label x-show="genOpdId" x-cloak class="mt-2 flex items-start gap-2 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 cursor-pointer">
                        <input type="checkbox" x-model="genConfirm" class="mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                        <span>Buat akun untuk <strong>semua pegawai</strong> di OPD ini yang belum punya akun. Bisa memakan waktu untuk OPD besar.</span>
                    </label>
                </div>

                <div x-show="genType === 'opd' && genMode === 'single'" x-cloak>
                    <x-input-label value="Pilih OPD" />
                    <select x-model="genOpdId"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-violet-500 focus:ring-violet-500 text-sm">
                        <option value="">-- Pilih OPD --</option>
                        <template x-for="opd in opdList" :key="opd.id">
                            <option :value="opd.id" x-text="opd.uraiunor"></option>
                        </template>
                    </select>
                </div>

                <div x-show="genType === 'opd' && genMode === 'all'" x-cloak>
                    <label class="flex items-start gap-2 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 cursor-pointer">
                        <input type="checkbox" x-model="genConfirm" class="mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                        <span>Buat akun Admin OPD untuk <strong>setiap OPD</strong> yang belum memiliki admin sama sekali.</span>
                    </label>
                </div>

                <div class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs space-y-1">
                    <p class="flex justify-between gap-3">
                        <span class="text-slate-400 shrink-0">Username</span>
                        <span class="font-mono font-medium text-slate-700 text-right" x-text="genUsernamePreview() ?? '—'"></span>
                    </p>
                    <p class="flex justify-between">
                        <span class="text-slate-400">Password default</span>
                        <span class="font-mono font-medium text-slate-700">sickep</span>
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="genOpen = false" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" :disabled="! genValid() || genSubmitting"
                            :class="genValid() && ! genSubmitting ? 'bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800' : 'bg-slate-300 cursor-not-allowed'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm transition">
                        <span x-text="genSubmitting ? 'Memproses...' : 'Generate'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
