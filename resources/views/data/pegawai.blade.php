<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Daftar Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        modal: null,
        nip: null,
        nama: null,
        openModal(type, nip, nama) { this.modal = type; this.nip = nip; this.nama = nama; },
        closeModal() { this.modal = null; this.nip = null; this.nama = null; },
    }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Total Pegawai</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['total']) }}</p>
                            <p class="mt-1 text-xs text-sky-100">Seluruh data SYSDB_PNS</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Pegawai Aktif</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['aktif']) }}</p>
                            <p class="mt-1 text-xs text-emerald-100">{{ $summary['aktif_pct'] }}% dari total</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="relative mt-4 h-1.5 w-full rounded-full bg-white/20 overflow-hidden">
                        <div class="h-full rounded-full bg-white" style="width: {{ $summary['aktif_pct'] }}%"></div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-500 to-slate-600 p-6 shadow-lg shadow-slate-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-200 uppercase tracking-wider">Tidak Aktif</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['tidak_aktif']) }}</p>
                            <p class="mt-1 text-xs text-slate-200">{{ $summary['tidak_aktif_pct'] }}% dari total</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                    </div>
                    <div class="relative mt-4 h-1.5 w-full rounded-full bg-white/20 overflow-hidden">
                        <div class="h-full rounded-full bg-white" style="width: {{ $summary['tidak_aktif_pct'] }}%"></div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('data.pegawai') }}" class="space-y-3">
                <div class="flex gap-3">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, NIP, atau OPD Induk..."
                           class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                        Cari
                    </button>
                </div>

                <div class="flex items-center gap-5 text-sm text-slate-600">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status:</span>
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

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Nama / NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jabatan / Unit Kerja</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">OPD Induk</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status</th>
                            @if (Auth::user()->isAdmin())
                                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pegawai as $row)
                            @php
                                $namaLengkap = trim(($row->pns_ftitle ? $row->pns_ftitle.' ' : '').($row->pns_pnsnam ?: '(tanpa nama)').($row->pns_rtitle ? ', '.$row->pns_rtitle : ''));
                            @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">{{ $namaLengkap }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->nip_baru ?: $row->pns_pnsnip }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <p class="text-slate-700">{{ $row->formasijabatan ?: '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $row->wku_name ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ $row->ins_insnam ?: '—' }}
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
                                @if (Auth::user()->isAdmin())
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($row->status_aktif === 'Aktif')
                                                <button type="button" title="Non Aktifkan Pegawai"
                                                        @click="openModal('nonaktifkan', '{{ $row->pns_pnsnip }}', @js($namaLengkap))"
                                                        class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v9" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button type="button" title="Aktifkan Kembali"
                                                        @click="openModal('aktifkan', '{{ $row->pns_pnsnip }}', @js($namaLengkap))"
                                                        class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            @endif
                                            <button type="button" title="Mutasi"
                                                    @click="openModal('mutasi', '{{ $row->pns_pnsnip }}', @js($namaLengkap))"
                                                    class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
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

        @if (Auth::user()->isAdmin())
            <div x-show="modal !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/50" @click="closeModal()"></div>

                <form x-show="modal === 'nonaktifkan'" x-cloak method="POST"
                      :action="'{{ url('pegawai') }}/' + nip + '/nonaktifkan'"
                      class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="q" value="{{ $q }}">
                    @foreach ($status as $s)
                        <input type="hidden" name="status[]" value="{{ $s }}">
                    @endforeach
                    <div>
                        <h3 class="font-semibold text-slate-800">Non Aktifkan Pegawai</h3>
                        <p class="text-sm text-slate-500 mt-1" x-text="nama"></p>
                    </div>
                    <div>
                        <x-input-label for="na_alasan" value="Alasan" />
                        <textarea id="na_alasan" name="alasan" rows="3" required
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                    </div>
                    <div>
                        <x-input-label for="na_tanggal" value="Tanggal Efektif" />
                        <input type="date" id="na_tanggal" name="tanggal_efektif" required
                               value="{{ now()->toDateString() }}"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                            Non Aktifkan
                        </button>
                    </div>
                </form>

                <form x-show="modal === 'aktifkan'" x-cloak method="POST"
                      :action="'{{ url('pegawai') }}/' + nip + '/aktifkan'"
                      class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="q" value="{{ $q }}">
                    @foreach ($status as $s)
                        <input type="hidden" name="status[]" value="{{ $s }}">
                    @endforeach
                    <div>
                        <h3 class="font-semibold text-slate-800">Aktifkan Kembali</h3>
                        <p class="text-sm text-slate-500 mt-1" x-text="nama"></p>
                    </div>
                    <div>
                        <x-input-label for="ak_alasan" value="Catatan (opsional)" />
                        <textarea id="ak_alasan" name="alasan" rows="2"
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                    </div>
                    <div>
                        <x-input-label for="ak_tanggal" value="Tanggal Efektif" />
                        <input type="date" id="ak_tanggal" name="tanggal_efektif" required
                               value="{{ now()->toDateString() }}"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                            Aktifkan
                        </button>
                    </div>
                </form>

                <form x-show="modal === 'mutasi'" x-cloak method="POST"
                      :action="'{{ url('pegawai') }}/' + nip + '/mutasi'"
                      class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                    @csrf
                    <input type="hidden" name="q" value="{{ $q }}">
                    @foreach ($status as $s)
                        <input type="hidden" name="status[]" value="{{ $s }}">
                    @endforeach
                    <div>
                        <h3 class="font-semibold text-slate-800">Mutasi Pegawai</h3>
                        <p class="text-sm text-slate-500 mt-1" x-text="nama"></p>
                    </div>
                    <div>
                        <x-input-label for="mt_opd" value="OPD Tujuan" />
                        <select id="mt_opd" name="opd_tujuan_kode" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">-- Pilih OPD --</option>
                            @foreach ($opdList as $opd)
                                <option value="{{ $opd->ins_inscod }}">{{ $opd->ins_insnam }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="mt_unit" value="Unit Kerja Tujuan (opsional)" />
                        <x-text-input id="mt_unit" name="unit_kerja_tujuan_nama" type="text" class="mt-1 block w-full" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="mt_nosk" value="Nomor SK" />
                            <x-text-input id="mt_nosk" name="nomor_sk" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="mt_tglsk" value="Tanggal SK" />
                            <input type="date" id="mt_tglsk" name="tanggal_sk"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <x-input-label for="mt_tglefektif" value="Tanggal Efektif" />
                        <input type="date" id="mt_tglefektif" name="tanggal_efektif" required
                               value="{{ now()->toDateString() }}"
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <x-input-label for="mt_ket" value="Keterangan (opsional)" />
                        <textarea id="mt_ket" name="keterangan" rows="2"
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal()"
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
        @endif
    </div>
</x-app-layout>
