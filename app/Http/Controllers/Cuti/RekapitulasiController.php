<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\TKodeCuti;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RekapitulasiController extends Controller
{
    protected const PER_PAGE = 10;

    protected int $tahun;

    protected string $jenisCuti;

    /**
     * Rekap cuti per OPD untuk satu tahun, opsional difilter jenis cuti.
     * Hanya cuti yang sudah disetujui penuh (atasan langsung & pejabat
     * berwenang) yang dihitung -- sama seperti Cari Cuti & Summary Cuti.
     */
    public function index(Request $request): View
    {
        $tahunList = $this->tahunList();

        $this->tahun = (int) $request->query('tahun', Carbon::today()->year);
        $this->jenisCuti = trim((string) $request->query('jenis_cuti', ''));
        $page = max(1, (int) $request->query('page', 1));

        [$jenisCutiList, $jenisCutiLabel] = $this->jenisCutiInfo();

        // Diambil mentah (bukan GROUP BY di SQL) supaya "Total Hari Cuti"
        // bisa dihitung dari hari kalender UNIK per pegawai -- SUM(lamahari)
        // bisa menghitung ganda kalau ada baris cuti yang tumpang tindih
        // tanggalnya untuk pegawai yang sama (mis. pengajuan yang direvisi).
        $raw = $this->baseQuery()
            ->selectRaw('
                COALESCE(i.ins_inscod, "") AS opd_kode,
                COALESCE(i.ins_insnam, "(Tanpa OPD)") AS opd_nama,
                r.nip, r.tanggalawalcltn, r.tanggalakhircltn
            ')
            ->get();

        $grouped = $raw->groupBy('opd_kode')->map(function (Collection $items) {
            return (object) [
                'opd_kode' => $items->first()->opd_kode,
                'opd_nama' => $items->first()->opd_nama,
                'jumlah_pegawai' => $items->pluck('nip')->unique()->count(),
                'jumlah_pengajuan' => $items->count(),
                'total_hari' => $this->hitungHariUnik($items),
            ];
        })->sortBy('opd_nama')->values();

        // Setiap nip hanya pernah masuk ke satu grup OPD (OPD pegawai bersifat
        // tetap per baris pegawai), jadi total keseluruhan cukup dijumlah dari
        // hasil per-OPD tanpa perlu memindai ulang seluruh baris mentah.
        $grand = (object) [
            'jumlah_pegawai' => $grouped->sum('jumlah_pegawai'),
            'jumlah_pengajuan' => $grouped->sum('jumlah_pengajuan'),
            'total_hari' => $grouped->sum('total_hari'),
        ];

        $rows = new LengthAwarePaginator(
            $grouped->forPage($page, static::PER_PAGE)->values(),
            $grouped->count(),
            static::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cuti.rekapitulasi', [
            'tahun' => $this->tahun,
            'tahunList' => $tahunList,
            'jenisCuti' => $this->jenisCuti,
            'jenisCutiList' => $jenisCutiList,
            'jenisCutiLabel' => $jenisCutiLabel,
            'rows' => $rows,
            // Cetak PDF selalu menampilkan seluruh baris hasil filter (bukan
            // cuma satu halaman) -- pagination hanya untuk tampilan layar.
            'allRows' => $grouped,
            'grand' => $grand,
            'dicetakPada' => now(),
        ]);
    }

    /**
     * Detail baris cuti (nama pegawai per pengajuan) untuk satu OPD hasil
     * rekap, dengan filter tahun & jenis cuti yang sama.
     */
    public function detail(Request $request): View
    {
        $tahunList = $this->tahunList();

        $this->tahun = (int) $request->query('tahun', Carbon::today()->year);
        $this->jenisCuti = trim((string) $request->query('jenis_cuti', ''));
        $opdKode = trim((string) $request->query('opd', ''));
        $page = max(1, (int) $request->query('page', 1));

        [, $jenisCutiLabel] = $this->jenisCutiInfo();

        $query = $this->baseQuery()
            ->when($opdKode !== '', fn ($q) => $q->where('i.ins_inscod', $opdKode))
            ->when($opdKode === '', fn ($q) => $q->whereNull('i.ins_inscod'));

        $opdNama = $opdKode !== ''
            ? (DB::table('sysdb_instansi')->where('ins_inscod', $opdKode)->value('ins_insnam') ?? $opdKode)
            : '(Tanpa OPD)';

        $total = (clone $query)->count();

        $offset = ($page - 1) * static::PER_PAGE;

        $detail = (clone $query)
            ->orderBy('r.tanggalawalcltn', 'desc')
            ->limit(static::PER_PAGE)
            ->offset($offset)
            ->get([
                'p.pns_pnsnam', 'p.pns_ftitle', 'p.pns_rtitle', 'p.nip_baru', 'p.pns_pnsnip',
                't.keterangancuti', 'r.tanggalawalcltn', 'r.tanggalakhircltn', 'r.lamahari',
            ]);

        $riwayat = new LengthAwarePaginator(
            $detail,
            $total,
            static::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Cetak PDF menampilkan seluruh baris (bukan cuma satu halaman).
        $riwayatSemua = (clone $query)
            ->orderBy('r.tanggalawalcltn', 'desc')
            ->get([
                'p.pns_pnsnam', 'p.pns_ftitle', 'p.pns_rtitle', 'p.nip_baru', 'p.pns_pnsnip',
                't.keterangancuti', 'r.tanggalawalcltn', 'r.tanggalakhircltn', 'r.lamahari',
            ]);

        return view('cuti.rekapitulasi-detail', [
            'tahun' => $this->tahun,
            'tahunList' => $tahunList,
            'jenisCuti' => $this->jenisCuti,
            'jenisCutiLabel' => $jenisCutiLabel,
            'opdKode' => $opdKode,
            'opdNama' => $opdNama,
            'riwayat' => $riwayat,
            'riwayatSemua' => $riwayatSemua,
            'totalHari' => $this->hitungHariUnik($riwayatSemua->map(fn ($r) => (object) [
                'nip' => $r->nip_baru ?: $r->pns_pnsnip,
                'tanggalawalcltn' => $r->tanggalawalcltn,
                'tanggalakhircltn' => $r->tanggalakhircltn,
            ])),
            'dicetakPada' => now(),
        ]);
    }

    /**
     * Hitung jumlah hari kalender unik per pegawai (pasangan nip+tanggal
     * tidak dihitung dua kali walau muncul di lebih dari satu baris cuti),
     * dibatasi ke rentang tahun yang sedang direkap.
     */
    protected function hitungHariUnik(Collection $items): int
    {
        $awalTahun = Carbon::create($this->tahun, 1, 1);
        $akhirTahun = Carbon::create($this->tahun, 12, 31);

        $hariUnik = [];

        foreach ($items as $item) {
            $mulai = Carbon::parse($item->tanggalawalcltn)->max($awalTahun);
            $selesai = $item->tanggalakhircltn
                ? Carbon::parse($item->tanggalakhircltn)->min($akhirTahun)
                : $mulai;

            if ($selesai->lt($mulai)) {
                continue;
            }

            foreach (CarbonPeriod::create($mulai, $selesai) as $tanggal) {
                $hariUnik[$item->nip.'|'.$tanggal->toDateString()] = true;
            }
        }

        return count($hariUnik);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: string}
     */
    protected function jenisCutiInfo(): array
    {
        $jenisCutiList = TKodeCuti::query()
            ->whereNotNull('keterangancuti')
            ->orderBy('keterangancuti')
            ->get(['kodecuti', 'keterangancuti']);

        $jenisCutiLabel = $this->jenisCuti !== ''
            ? ($jenisCutiList->firstWhere('kodecuti', $this->jenisCuti)->keterangancuti ?? $this->jenisCuti)
            : 'Semua Jenis Cuti';

        return [$jenisCutiList, $jenisCutiLabel];
    }

    protected function baseQuery(): Builder
    {
        return DB::table('riwayatcuti as r')
            ->join('sysdb_pns as p', 'p.nip_baru', '=', 'r.nip')
            ->leftJoin('tkodecuti as t', 't.kodecuti', '=', 'r.jeniscuti')
            ->leftJoin('sysdb_instansi as i', function (JoinClause $join) {
                $join->on('i.ins_inscod', '=', DB::raw('COALESCE(p.override_opd_kode, p.pns_inscod)'));
            })
            ->where('r.ansungsetuju', -1)
            ->where('r.pybsetuju', -1)
            ->whereBetween('r.tanggalawalcltn', ["{$this->tahun}-01-01", "{$this->tahun}-12-31"])
            ->when($this->jenisCuti !== '', fn ($q) => $q->where('r.jeniscuti', $this->jenisCuti));
    }

    /**
     * @return array<int, int>
     */
    protected function tahunList(): array
    {
        $tahunMax = Carbon::today()->addYear()->year;

        return DB::table('riwayatcuti')
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
    }
}
