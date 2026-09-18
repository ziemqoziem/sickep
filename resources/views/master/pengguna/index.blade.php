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
        form: { name: '', email: '', password: '', role: 'user' },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = { name: '', email: '', password: '', role: 'user' };
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = { name: row.name, email: row.email, password: '', role: row.role };
            this.formOpen = true;
        },
        openDelete(row) { this.target = row; this.deleteOpen = true; },
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Terverifikasi</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengguna as $row)
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
                                    @if ($row->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700 capitalize">
                                            {{ $row->role }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 capitalize">
                                            {{ $row->role }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-500">
                                    {{ $row->email_verified_at ? $row->email_verified_at->format('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" title="Edit" @click="openEdit(@js($row))"
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
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">

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
                        <option value="admin">Admin</option>
                    </select>
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
