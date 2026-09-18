@php
    $startNumber = method_exists($rows, 'firstItem') ? $rows->firstItem() : 1;
@endphp

<div class="space-y-4 print:space-y-2">
    @forelse ($rows as $i => $pegawai)
        <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden print:border print:border-black print:rounded-none print:shadow-none print:break-inside-avoid">
            <div class="flex items-center justify-between gap-3 px-5 py-3 bg-sky-50 border-b border-sky-100 print:bg-white print:border-black">
                <div class="flex items-center gap-3">
                    <span class="hidden sm:flex items-center justify-center w-7 h-7 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold print:hidden">
                        {{ $startNumber + $i }}
                    </span>
                    <div>
                        <p class="font-semibold text-slate-800 print:text-black">{{ $startNumber + $i }}. {{ $pegawai->nama }}</p>
                        <p class="text-xs text-slate-400 font-mono print:text-black">{{ $pegawai->nip }}</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs font-semibold text-sky-700 print:text-black">{{ $pegawai->jumlah_pengajuan }} pengajuan</p>
                    <p class="text-xs text-slate-500 print:text-black">{{ $pegawai->total_hari }} hari unik</p>
                </div>
            </div>

            <table class="min-w-full divide-y divide-slate-100 print:divide-y-0">
                <thead class="print:hidden">
                    <tr>
                        <th class="px-5 py-2 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jenis Cuti</th>
                        <th class="px-5 py-2 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-2 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Lama</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 print:divide-y-0">
                    @foreach ($pegawai->riwayat as $item)
                        <tr>
                            <td class="px-5 py-2 text-sm text-slate-600 print:text-black print:border print:border-black print:px-3 print:py-1">{{ $item->keterangancuti ?: '—' }}</td>
                            <td class="px-5 py-2 text-sm text-slate-600 print:text-black print:border print:border-black print:px-3 print:py-1">
                                {{ \Illuminate\Support\Carbon::parse($item->tanggalawalcltn)->translatedFormat('d M Y') }}
                                @if ($item->tanggalakhircltn)
                                    &ndash; {{ \Illuminate\Support\Carbon::parse($item->tanggalakhircltn)->translatedFormat('d M Y') }}
                                @endif
                            </td>
                            <td class="px-5 py-2 text-sm text-slate-600 text-right print:text-black print:border print:border-black print:px-3 print:py-1">{{ $item->lamahari }} hari</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="bg-white border border-sky-100 rounded-2xl px-6 py-6 text-center text-sm text-slate-400 print:border-black">
            Tidak ada data untuk filter yang dipilih.
        </div>
    @endforelse
</div>
