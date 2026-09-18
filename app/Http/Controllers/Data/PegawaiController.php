<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\SysdbInstansi;
use App\Models\SysdbPns;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        // Belum ada filter status yang dipilih sama sekali -> tampilkan semua.
        $status = $request->has('status') ? (array) $request->query('status') : ['Aktif', 'Tidak Aktif'];
        $status = array_values(array_intersect($status, ['Aktif', 'Tidak Aktif']));

        $pegawai = SysdbPns::query()
            ->leftJoin('kode_formasijabatan', 'kode_formasijabatan.kd_jabatan', '=', 'sysdb_pns.kode_formasi')
            ->leftJoin('sysdb_wkguni', function (JoinClause $join) {
                $join->on(
                    'sysdb_wkguni.wku_wrkcod',
                    '=',
                    DB::raw('COALESCE(sysdb_pns.override_unit_kerja_kode, sysdb_pns.pns_wkucod)')
                );
            })
            ->leftJoin('sysdb_instansi', function (JoinClause $join) {
                $join->on(
                    'sysdb_instansi.ins_inscod',
                    '=',
                    DB::raw('COALESCE(sysdb_pns.override_opd_kode, sysdb_pns.pns_inscod)')
                );
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('sysdb_pns.pns_pnsnam', 'like', "%{$q}%")
                        ->orWhere('sysdb_pns.pns_pnsnip', 'like', "%{$q}%")
                        ->orWhere('sysdb_pns.nip_baru', 'like', "%{$q}%")
                        ->orWhere('sysdb_instansi.ins_insnam', 'like', "%{$q}%");
                });
            })
            ->when(count($status) > 0 && count($status) < 2, function ($query) use ($status) {
                $query->whereIn('sysdb_pns.status_aktif', $status);
            })
            ->when(count($status) === 0, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('sysdb_instansi.ins_insnam')
            ->orderBy('sysdb_pns.pns_pnsnam')
            ->select([
                'sysdb_pns.pns_pnsnip',
                'sysdb_pns.nip_baru',
                'sysdb_pns.pns_pnsnam',
                'sysdb_pns.pns_ftitle',
                'sysdb_pns.pns_rtitle',
                'sysdb_pns.status_aktif',
                'kode_formasijabatan.formasijabatan',
                DB::raw('COALESCE(sysdb_pns.override_unit_kerja_nama, sysdb_wkguni.wku_name) AS wku_name'),
                'sysdb_instansi.ins_insnam',
            ])
            ->paginate(25)
            ->withQueryString();

        $total = SysdbPns::count();
        $aktif = SysdbPns::where('status_aktif', 'Aktif')->count();
        $tidakAktif = $total - $aktif;

        $summary = [
            'total' => $total,
            'aktif' => $aktif,
            'tidak_aktif' => $tidakAktif,
            'aktif_pct' => $total > 0 ? round($aktif / $total * 100) : 0,
            'tidak_aktif_pct' => $total > 0 ? round($tidakAktif / $total * 100) : 0,
        ];

        $opdList = SysdbInstansi::whereNotNull('ins_insnam')->orderBy('ins_insnam')->get(['ins_inscod', 'ins_insnam']);

        return view('data.pegawai', [
            'pegawai' => $pegawai,
            'q' => $q,
            'status' => $status,
            'summary' => $summary,
            'opdList' => $opdList,
        ]);
    }
}
