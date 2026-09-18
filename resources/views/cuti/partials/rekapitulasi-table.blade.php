@php
    $startNumber = method_exists($rows, 'firstItem') ? $rows->firstItem() : 1;
@endphp

<div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden print:border-0 print:rounded-none print:shadow-none">
    <table class="min-w-full divide-y divide-sky-100 print:divide-y-0 print:border print:border-black">
        <thead class="bg-sky-50 print:bg-white">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">OPD</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Jumlah Pegawai</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Jumlah Pengajuan</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider print:text-black print:border print:border-black print:px-3 print:py-2">Total Hari Cuti</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-sky-700 uppercase tracking-wider print:hidden">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 print:divide-y-0">
            @forelse ($rows as $i => $row)
                <tr class="print:break-inside-avoid">
                    <td class="px-6 py-3 text-sm text-slate-500 print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ $startNumber + $i }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-slate-800 print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ $row->opd_nama }}</td>
                    <td class="px-6 py-3 text-sm text-slate-600 text-right print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ number_format($row->jumlah_pegawai) }}</td>
                    <td class="px-6 py-3 text-sm text-slate-600 text-right print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ number_format($row->jumlah_pengajuan) }}</td>
                    <td class="px-6 py-3 text-sm text-slate-600 text-right print:text-black print:border print:border-black print:px-3 print:py-1.5">{{ number_format($row->total_hari) }} hari</td>
                    <td class="px-6 py-3 text-right print:hidden">
                        <a href="{{ route('rekapitulasi.detail', ['tahun' => $tahun, 'jenis_cuti' => $jenisCuti, 'opd' => $row->opd_kode]) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:text-sky-800 transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-400 print:text-black print:border print:border-black">
                        Tidak ada data untuk tahun dan jenis cuti yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr class="bg-sky-50 print:bg-white font-semibold">
                    <td colspan="2" class="px-6 py-3 text-sm text-slate-800 print:text-black print:border print:border-black print:px-3 print:py-2">TOTAL</td>
                    <td class="px-6 py-3 text-sm text-slate-800 text-right print:text-black print:border print:border-black print:px-3 print:py-2">{{ number_format($grand->jumlah_pegawai) }}</td>
                    <td class="px-6 py-3 text-sm text-slate-800 text-right print:text-black print:border print:border-black print:px-3 print:py-2">{{ number_format($grand->jumlah_pengajuan) }}</td>
                    <td class="px-6 py-3 text-sm text-slate-800 text-right print:text-black print:border print:border-black print:px-3 print:py-2">{{ number_format($grand->total_hari) }} hari</td>
                    <td class="print:hidden"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
