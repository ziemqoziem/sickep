<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Riwayat Cuti Saya') }}
        </h2>
    </x-slot>

    <div class="py-10">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 to-sky-700 p-6 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                    <p class="text-xs font-semibold text-sky-100 uppercase tracking-wider">Sisa Cuti Tahunan {{ now()->year }}</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $sisaCutiTahunan }} hari</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-600 to-slate-700 p-6 shadow-lg shadow-slate-600/20 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-200 uppercase tracking-wider">Ajukan Cuti Baru</p>
                        <p class="mt-1 text-sm text-slate-100">Isi form pengajuan cuti</p>
                    </div>
                    <a href="{{ route('cuti-baru.ajukan') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white/15 hover:bg-white/25 px-4 py-2 text-sm font-semibold text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajukan
                    </a>
                </div>
            </div>

            @if (! $pegawai)
                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm px-4 py-3">
                    Akun Anda belum ditautkan ke data pegawai, sehingga belum bisa mengajukan cuti. Hubungi Admin
                    untuk menautkan akun Anda di Master Pengguna.
                </div>
            @endif

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">No. Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($riwayat as $row)
                            <tr>
                                <td class="px-6 py-3 text-sm font-mono text-slate-600">{{ $row->nomor_pengajuan }}</td>
                                <td class="px-6 py-3 text-sm text-slate-700">{{ $row->jenisCutiAturan->nama }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ $row->tanggal_mulai->translatedFormat('d M Y') }}
                                    @if ($row->tanggal_selesai)
                                        &ndash; {{ $row->tanggal_selesai->translatedFormat('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @php
                                        $label = $row->labelStatusJenjang();
                                        $color = match (true) {
                                            $row->status === 'disetujui' => 'bg-emerald-50 text-emerald-700',
                                            $row->status === 'ditolak' => 'bg-red-50 text-red-700',
                                            $row->status === 'dibatalkan' => 'bg-slate-100 text-slate-500',
                                            default => 'bg-amber-50 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('cuti-baru.show', $row) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition">
                                            Detail
                                        </a>
                                        @if ($row->status === 'diajukan')
                                            <form method="POST" action="{{ route('cuti-baru.batalkan', $row) }}"
                                                  onsubmit="return confirm('Batalkan pengajuan {{ $row->nomor_pengajuan }}?');">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">
                                    Belum ada pengajuan cuti.
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
