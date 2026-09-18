<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Cari Cuti') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-slate-500">
                Menampilkan riwayat cuti yang telah disetujui penuh (atasan langsung &amp; pejabat berwenang).
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Total Hasil</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['total']) }}</p>
                            <p class="mt-1 text-xs text-sky-100">Riwayat cuti ditemukan</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 p-6 shadow-lg shadow-rose-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-rose-100 uppercase tracking-wider">Sedang Cuti</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['sedang_cuti']) }}</p>
                            <p class="mt-1 text-xs text-rose-100">Mencakup hari ini</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-6 shadow-lg shadow-violet-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-violet-100 uppercase tracking-wider">Total Hari Cuti</p>
                            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['total_hari']) }}</p>
                            <p class="mt-1 text-xs text-violet-100">Akumulasi dari hasil pencarian</p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('cari-cuti') }}" class="flex gap-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari NIP, nama, atau OPD Induk..."
                       class="flex-1 rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                <select name="tahun" class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <option value="">Semua Tahun</option>
                    @foreach ($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahun === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                    Cari
                </button>
            </form>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Nama / NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">OPD Induk</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Lama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($riwayat as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm">
                                    <p class="font-medium text-slate-800">
                                        {{ trim(($row->pns_ftitle ? $row->pns_ftitle.' ' : '').($row->pns_pnsnam ?: '(tanpa nama)').($row->pns_rtitle ? ', '.$row->pns_rtitle : '')) }}
                                    </p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $row->nip_baru ?: $row->pns_pnsnip }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $row->ins_insnam ?: '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $row->keterangancuti ?: '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ \Illuminate\Support\Carbon::parse($row->tanggalawalcltn)->translatedFormat('d M Y') }}
                                    @if ($row->tanggalakhircltn)
                                        &ndash; {{ \Illuminate\Support\Carbon::parse($row->tanggalakhircltn)->translatedFormat('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $row->lamahari }} hari</td>
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

            {{ $riwayat->links() }}
        </div>
    </div>
</x-app-layout>
