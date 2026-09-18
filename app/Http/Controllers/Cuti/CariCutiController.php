<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CariCutiController extends Controller
{
    protected const PER_PAGE = 25;

    /**
     * riwayatcuti (123rb+ baris) butuh strategi berbeda tergantung filter:
     *
     * - Tanpa filter nama/OPD: riwayatcuti harus jadi tabel penggerak JOIN
     *   (STRAIGHT_JOIN) supaya index tanggalawalcltn dipakai untuk
     *   ORDER BY + LIMIT, bukan filesort di atas seluruh dataset.
     * - Dengan filter nama/OPD: cari dulu NIP yang cocok di tabel kecil
     *   (sysdb_pns/sysdb_instansi), baru query riwayatcuti WHERE nip IN (...)
     *   -- kalau riwayatcuti tetap jadi penggerak, MySQL bisa scan sangat
     *   banyak baris hanya untuk menemukan sedikit yang cocok nama.
     *
     * Hanya cuti yang sudah disetujui penuh (atasan langsung & pejabat
     * berwenang) yang ditampilkan. Batas tanggal +1 tahun membuang satu
     * baris anomali data sumber (tahun ketik salah, mis. 7022).
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $tahun = $request->query('tahun', '');
        $tahun = $tahun !== '' ? (int) $tahun : null;
        $page = max(1, (int) $request->query('page', 1));
        $maxDate = Carbon::today()->addYear()->toDateString();
        $today = Carbon::today()->toDateString();

        $nipList = null;

        if ($q !== '') {
            $like = "%{$q}%";
            $nipList = DB::table('sysdb_pns as p')
                ->leftJoin('sysdb_instansi as i', function ($join) {
                    $join->on('i.ins_inscod', '=', DB::raw('COALESCE(p.override_opd_kode, p.pns_inscod)'));
                })
                ->where(function ($sub) use ($like) {
                    $sub->where('p.pns_pnsnam', 'like', $like)
                        ->orWhere('p.nip_baru', 'like', $like)
                        ->orWhere('p.pns_pnsnip', 'like', $like)
                        ->orWhere('i.ins_insnam', 'like', $like);
                })
                ->whereNotNull('p.nip_baru')
                ->pluck('p.nip_baru');

            if ($nipList->isEmpty()) {
                $riwayat = new LengthAwarePaginator([], 0, static::PER_PAGE, $page, ['path' => $request->url()]);

                return view('cuti.index', [
                    'q' => $q,
                    'tahun' => $tahun,
                    'tahunList' => $this->tahunList(),
                    'summary' => ['total' => 0, 'sedang_cuti' => 0, 'total_hari' => 0],
                    'riwayat' => $riwayat,
                ]);
            }
        }

        $joinSql = '
            FROM riwayatcuti r
            '.($nipList === null ? 'STRAIGHT_JOIN' : 'JOIN').' sysdb_pns p ON p.nip_baru = r.nip
            LEFT JOIN tkodecuti t ON t.kodecuti = r.jeniscuti
            LEFT JOIN sysdb_instansi i ON i.ins_inscod = COALESCE(p.override_opd_kode, p.pns_inscod)
        ';

        $where = 'r.ansungsetuju = -1 AND r.pybsetuju = -1 AND r.tanggalawalcltn <= ?';
        $bindings = [$maxDate];

        if ($tahun !== null) {
            // whereBetween tanggal (bukan YEAR()) supaya index tanggal tetap terpakai.
            $where .= ' AND r.tanggalawalcltn BETWEEN ? AND ?';
            array_push($bindings, "{$tahun}-01-01", "{$tahun}-12-31");
        }

        if ($nipList !== null) {
            $placeholders = implode(',', array_fill(0, $nipList->count(), '?'));
            $where .= " AND r.nip IN ({$placeholders})";
            array_push($bindings, ...$nipList->all());
        }

        $aggregate = DB::selectOne('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN r.tanggalawalcltn <= ? AND (r.tanggalakhircltn >= ? OR r.tanggalakhircltn IS NULL) THEN 1 ELSE 0 END) AS sedang_cuti,
                COALESCE(SUM(r.lamahari), 0) AS total_hari
            '.$joinSql.'
            WHERE '.$where,
            array_merge([$today, $today], $bindings)
        );

        $summary = [
            'total' => (int) $aggregate->total,
            'sedang_cuti' => (int) $aggregate->sedang_cuti,
            'total_hari' => (int) $aggregate->total_hari,
        ];

        $offset = ($page - 1) * static::PER_PAGE;

        $rows = DB::select('
            SELECT p.pns_pnsnam, p.pns_ftitle, p.pns_rtitle, p.nip_baru, p.pns_pnsnip,
                   i.ins_insnam, t.keterangancuti, r.tanggalawalcltn, r.tanggalakhircltn, r.lamahari
            '.$joinSql.'
            WHERE '.$where.'
            ORDER BY r.tanggalawalcltn DESC
            LIMIT '.static::PER_PAGE.' OFFSET '.$offset,
            $bindings
        );

        $riwayat = new LengthAwarePaginator(
            $rows,
            $summary['total'],
            static::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cuti.index', [
            'q' => $q,
            'tahun' => $tahun,
            'tahunList' => $this->tahunList(),
            'summary' => $summary,
            'riwayat' => $riwayat,
        ]);
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
