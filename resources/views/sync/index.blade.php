<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Sync Data') }}
            </h2>

            <form method="POST" action="{{ route('sync-data.run', 'all') }}"
                  onsubmit="return confirm('Jalankan sinkronisasi untuk semua data? Proses ini bisa memakan waktu beberapa menit.');">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Semua
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('sync_success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('sync_success') }}
                </div>
            @endif

            @if (session('sync_error'))
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    {{ session('sync_error') }}
                </div>
            @endif

            <p class="text-sm text-slate-500">
                Daftar data yang disinkronkan dari database sumber (SQL Server) beserta status dan waktu sinkronisasi terakhir.
                Konfigurasi koneksi dapat diatur di menu <span class="font-medium text-sky-700">Setting Koneksi DB</span>.
            </p>

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Jumlah Baris</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Terakhir Sinkron</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Watermark</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rows as $row)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                    {{ $row['label'] }}
                                    @if ($row['incremental'])
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-600" title="Mendukung sync incremental berbasis modidatetime">
                                            incremental
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $badge = match ($row['status']) {
                                            'success' => 'bg-emerald-50 text-emerald-700',
                                            'failed' => 'bg-red-50 text-red-700',
                                            'running' => 'bg-amber-50 text-amber-700',
                                            default => 'bg-slate-100 text-slate-500',
                                        };
                                        $label = match ($row['status']) {
                                            'success' => 'Berhasil',
                                            'failed' => 'Gagal',
                                            'running' => 'Berjalan',
                                            'pending' => 'Menunggu',
                                            default => 'Belum pernah',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                    @if ($row['status'] === 'failed' && $row['message'])
                                        <p class="mt-1 text-xs text-red-500 max-w-xs truncate" title="{{ $row['message'] }}">
                                            {{ $row['message'] }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $row['records_synced'] !== null ? number_format($row['records_synced']) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $row['finished_at']?->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $row['watermark']?->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('sync-data.run', $row['entity']) }}"
                                              onsubmit="return confirm('Jalankan sinkronisasi {{ $row['label'] }} sekarang?{{ $row['incremental'] ? ' (incremental -- cuma baris yang berubah)' : '' }}');">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-white border border-sky-300 rounded-md font-semibold text-xs text-sky-700 hover:bg-sky-50 transition">
                                                Sync
                                            </button>
                                        </form>

                                        @if ($row['incremental'])
                                            <form method="POST" action="{{ route('sync-data.run', $row['entity']) }}"
                                                  onsubmit="return confirm('Jalankan full scan {{ $row['label'] }}? Mengabaikan watermark, memproses ulang seluruh data dari sumber -- bisa lebih lama.');">
                                                @csrf
                                                <input type="hidden" name="full" value="1">
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-md font-semibold text-xs text-slate-600 hover:bg-slate-50 transition">
                                                    Full
                                                </button>
                                            </form>
                                        @endif

                                        @if ($row['entity'] === 'riwayatcuti')
                                            <form method="POST" action="{{ route('sync-data.run', $row['entity']) }}"
                                                  onsubmit="return confirm('Full scan {{ $row['label'] }} DAN hapus baris lokal yang sudah tidak ada di sumber? Tindakan ini menghapus data histori yang dianggap sudah dibatalkan di sumber. Lanjutkan?');">
                                                @csrf
                                                <input type="hidden" name="prune" value="1">
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-white border border-red-200 rounded-md font-semibold text-xs text-red-600 hover:bg-red-50 transition">
                                                    Prune
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
