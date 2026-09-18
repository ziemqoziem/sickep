<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected int $tahun;

    public function index(Request $request): View
    {
        $today = Carbon::today();
        $tahunSekarang = $today->year;

        $this->tahun = (int) $request->query('tahun', $tahunSekarang);

        $totalCuti = $this->baseQuery()->count();

        $pegawaiUnik = $this->baseQuery()->distinct('nip')->count('nip');

        // "Sedang cuti hari ini" selalu real-time, tidak ikut terfilter tahun.
        $sedangCutiList = $this->sedangCutiHariIni($today);

        $jenisTerbanyak = $this->baseQuery()
            ->leftJoin('tkodecuti', 'tkodecuti.kodecuti', '=', 'riwayatcuti.jeniscuti')
            ->selectRaw('COALESCE(tkodecuti.keterangancuti, riwayatcuti.jeniscuti, "(tanpa jenis)") AS nama, COUNT(*) AS jumlah')
            ->groupBy('nama')
            ->orderByDesc('jumlah')
            ->first();

        $summary = [
            'total' => $totalCuti,
            'pegawai_unik' => $pegawaiUnik,
            'sedang_cuti' => count($sedangCutiList),
            'jenis_terbanyak' => $jenisTerbanyak->nama ?? '—',
        ];

        return view('dashboard', [
            'summary' => $summary,
            'tren' => $this->trenBulanan(),
            'trenPegawai' => $this->trenPegawaiBulanan(),
            'jenisCuti' => $this->distribusiJenisCuti(),
            'topOpd' => $this->topOpd(),
            'terbaru' => $this->riwayatTerbaru(),
            'sedangCutiList' => $sedangCutiList,
            'tahun' => $this->tahun,
            'tahunList' => $this->tahunList(),
        ]);
    }

    protected function sedangCutiHariIni(Carbon $today): array
    {
        return DB::table('riwayatcuti')
            ->join('sysdb_pns', 'sysdb_pns.nip_baru', '=', 'riwayatcuti.nip')
            ->leftJoin('tkodecuti', 'tkodecuti.kodecuti', '=', 'riwayatcuti.jeniscuti')
            ->leftJoin('sysdb_instansi', function (JoinClause $join) {
                $join->on(
                    'sysdb_instansi.ins_inscod',
                    '=',
                    DB::raw('COALESCE(sysdb_pns.override_opd_kode, sysdb_pns.pns_inscod)')
                );
            })
            ->where('riwayatcuti.ansungsetuju', -1)
            ->where('riwayatcuti.pybsetuju', -1)
            ->where('riwayatcuti.tanggalawalcltn', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->where('riwayatcuti.tanggalakhircltn', '>=', $today->toDateString())
                    ->orWhereNull('riwayatcuti.tanggalakhircltn');
            })
            ->orderBy('sysdb_pns.pns_pnsnam')
            ->get([
                'sysdb_pns.pns_pnsnam',
                'sysdb_pns.nip_baru',
                'sysdb_pns.pns_pnsnip',
                'sysdb_instansi.ins_insnam',
                'tkodecuti.keterangancuti',
                'riwayatcuti.tanggalawalcltn',
                'riwayatcuti.tanggalakhircltn',
            ])
            ->toArray();
    }

    /**
     * Riwayat cuti yang sudah disetujui penuh (atasan langsung & pejabat
     * berwenang), dibatasi ke tahun yang dipilih.
     */
    protected function baseQuery(): Builder
    {
        // whereBetween tanggal (bukan whereYear) supaya index
        // riwayatcuti_tanggalawalcltn_index tetap bisa dipakai --
        // whereYear() membungkus kolom dengan fungsi dan memaksa full scan.
        return DB::table('riwayatcuti')
            ->where('ansungsetuju', -1)
            ->where('pybsetuju', -1)
            ->whereBetween('tanggalawalcltn', ["{$this->tahun}-01-01", "{$this->tahun}-12-31"]);
    }

    /**
     * @return array<int, int>
     */
    protected function tahunList(): array
    {
        $tahunMax = Carbon::today()->addYear()->year;

        $years = DB::table('riwayatcuti')
            ->where('ansungsetuju', -1)
            ->where('pybsetuju', -1)
            ->where('tanggalawalcltn', '<=', Carbon::today()->addYear()->toDateString())
            ->selectRaw('DISTINCT YEAR(tanggalawalcltn) AS thn')
            ->orderByDesc('thn')
            ->pluck('thn')
            ->map(fn ($y) => (int) $y)
            ->filter(fn ($y) => $y >= 2015 && $y <= $tahunMax)
            ->values()
            ->all();

        return $years;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function trenBulanan(): array
    {
        $rows = $this->baseQuery()
            ->selectRaw("MONTH(tanggalawalcltn) AS bulan, COUNT(*) AS jumlah")
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'label' => Carbon::create($this->tahun, $m, 1)->translatedFormat('M'),
                'value' => (int) ($rows[$m] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function trenPegawaiBulanan(): array
    {
        $rows = $this->baseQuery()
            ->selectRaw('MONTH(tanggalawalcltn) AS bulan, COUNT(DISTINCT nip) AS jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'label' => Carbon::create($this->tahun, $m, 1)->translatedFormat('M'),
                'value' => (int) ($rows[$m] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function distribusiJenisCuti(): array
    {
        $rows = $this->baseQuery()
            ->leftJoin('tkodecuti', 'tkodecuti.kodecuti', '=', 'riwayatcuti.jeniscuti')
            ->selectRaw('COALESCE(tkodecuti.keterangancuti, riwayatcuti.jeniscuti, "(tanpa jenis)") AS nama, COUNT(*) AS jumlah')
            ->groupBy('nama')
            ->orderByDesc('jumlah')
            ->get();

        $top = $rows->take(7);
        $sisa = $rows->slice(7)->sum('jumlah');

        $result = $top->map(fn ($r) => ['label' => $r->nama, 'value' => (int) $r->jumlah])->values()->all();

        if ($sisa > 0) {
            $result[] = ['label' => 'Lainnya', 'value' => (int) $sisa];
        }

        return $result;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function topOpd(): array
    {
        $rows = $this->baseQuery()
            ->join('sysdb_pns', 'sysdb_pns.nip_baru', '=', 'riwayatcuti.nip')
            ->leftJoin('sysdb_instansi', function (JoinClause $join) {
                $join->on(
                    'sysdb_instansi.ins_inscod',
                    '=',
                    DB::raw('COALESCE(sysdb_pns.override_opd_kode, sysdb_pns.pns_inscod)')
                );
            })
            ->selectRaw('COALESCE(sysdb_instansi.ins_insnam, "(tanpa OPD)") AS nama, COUNT(*) AS jumlah')
            ->groupBy('nama')
            ->orderByDesc('jumlah')
            ->limit(8)
            ->get();

        return $rows->map(fn ($r) => ['label' => $r->nama, 'value' => (int) $r->jumlah])->values()->all();
    }

    protected function riwayatTerbaru(): array
    {
        return $this->baseQuery()
            ->join('sysdb_pns', 'sysdb_pns.nip_baru', '=', 'riwayatcuti.nip')
            ->leftJoin('tkodecuti', 'tkodecuti.kodecuti', '=', 'riwayatcuti.jeniscuti')
            ->leftJoin('sysdb_instansi', function (JoinClause $join) {
                $join->on(
                    'sysdb_instansi.ins_inscod',
                    '=',
                    DB::raw('COALESCE(sysdb_pns.override_opd_kode, sysdb_pns.pns_inscod)')
                );
            })
            ->orderByDesc('riwayatcuti.tanggalawalcltn')
            ->limit(10)
            ->get([
                'sysdb_pns.pns_pnsnam',
                'sysdb_pns.nip_baru',
                'sysdb_pns.pns_pnsnip',
                'sysdb_instansi.ins_insnam',
                'tkodecuti.keterangancuti',
                'riwayatcuti.tanggalawalcltn',
                'riwayatcuti.tanggalakhircltn',
                'riwayatcuti.lamahari',
            ])
            ->toArray();
    }
}
