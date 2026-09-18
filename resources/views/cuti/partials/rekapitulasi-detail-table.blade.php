@php
    $startNumber = method_exists($riwayat, 'firstItem') ? $riwayat->firstItem() : 1;
@endphp

<div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden print:border-0 print:rounded-none print:shadow-none">
    <table class="min-w-full divide-y divide-sky-100 print:divide-y-0 print:border print:border-black">
        <thead class="bg-sky-50 print:bg-white">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Nama / NIP</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Jenis Cuti</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Tanggal</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Lama</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 print:divide-y-0">
            @forelse ($riwayat as $i => $row)
                <tr class="print:break-inside-avoid">
                    <td class="px-6 py-3 text-sm text-slate-500 print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ $startNumber + $i }}</td>
                    <td class="px-6 py-3 text-sm print:border print:border-black print:px-3 print:py-1.5">
                        <p class="font-medium text-slate-800 print:text-black">
                            {{ trim(($row->pns_ftitle ? $row->pns_ftitle.' ' : '').($row->pns_pnsnam ?: '(tanpa nama)').($row->pns_rtitle ? ', '.$row->pns_rtitle : '')) }}
                        </p>
                        <p class="text-xs text-slate-400 font-mono print:text-black">{{ $row->nip_baru ?: $row->pns_pnsnip }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-600 print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ $row->keterangancuti ?: '—' }}</td>
                    <td class="px-6 py-3 text-sm text-slate-600 print:text-black print:border print:border-black print:px-3 print:py-1.5">
                        {{ \Illuminate\Support\Carbon::parse($row->tanggalawalcltn)->translatedFormat('d M Y') }}
                        @if ($row->tanggalakhircltn)
                            &ndash; {{ \Illuminate\Support\Carbon::parse($row->tanggalakhircltn)->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-600 text-right print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ $row->lamahari }} hari</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400 print:text-black print:border print:border-black">
                        Tidak ada data untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
