<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Broadcast Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        formOpen: false,
        deleteOpen: false,
        mode: 'create',
        target: null,
        form: { judul: '', isi: '', tanggal_mulai: '', tanggal_selesai: '', aktif: true },
        openCreate() {
            this.mode = 'create';
            this.target = null;
            this.form = { judul: '', isi: '', tanggal_mulai: '', tanggal_selesai: '', aktif: true };
            this.formOpen = true;
        },
        openEdit(row) {
            this.mode = 'edit';
            this.target = row;
            this.form = {
                judul: row.judul, isi: row.isi,
                tanggal_mulai: row.tanggal_mulai, tanggal_selesai: row.tanggal_selesai,
                aktif: !!row.aktif,
            };
            this.formOpen = true;
        },
        openDelete(row) { this.target = row; this.deleteOpen = true; },
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 p-6 sm:p-8 shadow-lg shadow-indigo-600/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg sm:text-xl font-bold text-white">Broadcast Pengumuman</p>
                        <p class="text-sm text-indigo-100 mt-0.5">Muncul sebagai pop up ke pengguna setelah login, selama tanggal hari ini ada dalam rentang aktif</p>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('master.pengumuman') }}" class="flex gap-3 flex-1 max-w-md">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul pengumuman..."
                           @input.debounce.500ms="$el.form.requestSubmit()"
                           class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                        Cari
                    </button>
                </form>

                <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-600/20 hover:from-indigo-700 hover:to-violet-800 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pengumuman
                </button>
            </div>

            <div class="space-y-3">
                @forelse ($pengumuman as $row)
                    @php
                        $status = $row->statusRentang();
                        $statusWarna = match ($status) {
                            'berjalan' => ['badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500', 'label' => 'Sedang Tayang'],
                            'terjadwal' => ['badge' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500', 'label' => 'Terjadwal'],
                            'berakhir' => ['badge' => 'bg-slate-100 text-slate-500', 'dot' => 'bg-slate-400', 'label' => 'Berakhir'],
                            default => ['badge' => 'bg-red-50 text-red-600', 'dot' => 'bg-red-500', 'label' => 'Nonaktif'],
                        };
                        $rowPayload = [
                            'id' => $row->id,
                            'judul' => $row->judul,
                            'isi' => $row->isi,
                            'tanggal_mulai' => $row->tanggal_mulai->format('Y-m-d'),
                            'tanggal_selesai' => $row->tanggal_selesai->format('Y-m-d'),
                            'aktif' => $row->aktif,
                        ];
                    @endphp
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-slate-800">{{ $row->judul }}</p>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusWarna['badge'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusWarna['dot'] }}"></span>
                                        {{ $statusWarna['label'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1.5 line-clamp-2">{{ $row->isi }}</p>
                                <p class="text-xs text-slate-400 mt-2">
                                    {{ $row->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $row->tanggal_selesai->translatedFormat('d M Y') }}
                                    @if ($row->pembuat)
                                        &middot; dibuat oleh {{ $row->pembuat->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" title="Edit" @click="openEdit(@js($rowPayload))"
                                        class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" title="Hapus" @click="openDelete(@js($rowPayload))"
                                        class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm px-6 py-12 text-center">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <p class="text-sm text-slate-400">Belum ada pengumuman.</p>
                    </div>
                @endforelse
            </div>

            {{ $pengumuman->links() }}
        </div>

        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="formOpen = false"></div>

            <form method="POST"
                  :action="mode === 'create' ? '{{ route('master.pengumuman.store') }}' : '{{ url('master/pengumuman') }}/' + (target ? target.id : '')"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="q" value="{{ $q }}">

                <div>
                    <h3 class="font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Pengumuman' : 'Edit Pengumuman'"></h3>
                </div>

                <div>
                    <x-input-label value="Judul" />
                    <input type="text" name="judul" x-model="form.judul" required maxlength="200"
                           class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                </div>
                <div>
                    <x-input-label value="Isi Pengumuman" />
                    <textarea name="isi" x-model="form.isi" rows="5" required maxlength="5000"
                              class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Tanggal Mulai Aktif" />
                        <input type="date" name="tanggal_mulai" x-model="form.tanggal_mulai" required
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label value="Tanggal Selesai Aktif" />
                        <input type="date" name="tanggal_selesai" x-model="form.tanggal_selesai" required :min="form.tanggal_mulai"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                </div>
                <p class="text-xs text-slate-400 -mt-2">Pop up hanya muncul kalau tanggal hari ini ada di antara tanggal mulai &amp; selesai di atas.</p>

                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="aktif" x-model="form.aktif" value="1"
                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <span class="text-sm text-slate-600">Aktifkan pengumuman ini</span>
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="formOpen = false"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-700 hover:to-violet-800 shadow-sm shadow-indigo-600/20 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50" @click="deleteOpen = false"></div>

            <form method="POST" :action="target ? '{{ url('master/pengumuman') }}/' + target.id : '#'"
                  class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
                @csrf
                @method('DELETE')
                <input type="hidden" name="q" value="{{ $q }}">
                <div>
                    <h3 class="font-semibold text-slate-800">Hapus Pengumuman</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Yakin ingin menghapus pengumuman <span class="font-medium" x-text="target?.judul"></span>?
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
