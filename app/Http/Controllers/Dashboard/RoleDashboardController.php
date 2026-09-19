<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CutiPengajuan;
use App\Models\CutiPengajuanTahap;
use App\Models\CutiSaldoTahunan;
use App\Models\MasterOpd;
use App\Models\MasterPegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', $this->adminData());
        }

        if ($user->isOpd()) {
            return view('dashboard.opd', $this->opdData($user));
        }

        return view('dashboard.user', $this->userData($user));
    }

    protected function userData(User $user): array
    {
        $pegawai = $user->pegawai;
        $tahun = now()->year;

        if (! $pegawai) {
            return [
                'pegawai' => null,
                'sisaCutiTahunan' => 0,
                'rekap' => ['diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'dibatalkan' => 0, 'total' => 0],
                'riwayatTerbaru' => collect(),
                'tahun' => $tahun,
            ];
        }

        $saldo = CutiSaldoTahunan::where('pegawai_id', $pegawai->id)->where('tahun', $tahun)->first();

        $rekap = $this->rekapStatus(
            CutiPengajuan::where('pegawai_id', $pegawai->id)->whereYear('tanggal_mulai', $tahun)
        );

        $riwayatTerbaru = CutiPengajuan::where('pegawai_id', $pegawai->id)
            ->with('jenisCutiAturan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'pegawai' => $pegawai,
            'sisaCutiTahunan' => $saldo->sisa ?? $pegawai->sisa_cuti_tahunan ?? 12,
            'rekap' => $rekap,
            'riwayatTerbaru' => $riwayatTerbaru,
            'tahun' => $tahun,
        ];
    }

    protected function opdData(User $user): array
    {
        $opdList = $user->opdDiampu()->orderBy('uraiunor')->get();
        $opdIds = $opdList->pluck('id');
        $tahun = now()->year;

        $base = fn () => CutiPengajuan::whereHas('pegawai', fn (Builder $q) => $q->whereIn('opd_id', $opdIds))
            ->whereYear('tanggal_mulai', $tahun);

        $rekap = $this->rekapStatus($base());

        $pegawaiCount = MasterPegawai::whereIn('opd_id', $opdIds)->count();

        $menungguSaya = CutiPengajuanTahap::where('jenjang', 'kepala_unit_kerja')
            ->where('status', 'menunggu')
            ->whereHas('cutiPengajuan.pegawai', fn (Builder $q) => $q->whereIn('opd_id', $opdIds))
            ->count();

        $jenisCuti = $this->distribusiJenisCuti($base());
        $tren = $this->trenBulanan($base(), $tahun);

        $terbaru = $base()->with(['pegawai', 'jenisCutiAturan'])->orderByDesc('created_at')->limit(8)->get();

        return [
            'opdList' => $opdList,
            'pegawaiCount' => $pegawaiCount,
            'rekap' => $rekap,
            'menungguSaya' => $menungguSaya,
            'jenisCuti' => $jenisCuti,
            'tren' => $tren,
            'terbaru' => $terbaru,
            'tahun' => $tahun,
        ];
    }

    protected function adminData(): array
    {
        $tahun = now()->year;

        $base = fn () => CutiPengajuan::whereYear('tanggal_mulai', $tahun);

        $rekap = $this->rekapStatus($base());

        $menungguFinal = CutiPengajuanTahap::where('jenjang', 'admin')->where('status', 'menunggu')->count();

        $jenisCuti = $this->distribusiJenisCuti($base());

        $topOpd = $base()
            ->join('tb_pegawai_aktif', 'tb_pegawai_aktif.id', '=', 'cuti_pengajuan.pegawai_id')
            ->join('tb_opd_aktif', 'tb_opd_aktif.id', '=', 'tb_pegawai_aktif.opd_id')
            ->selectRaw('tb_opd_aktif.uraiunor AS label, COUNT(*) AS value')
            ->groupBy('tb_opd_aktif.uraiunor')
            ->orderByDesc('value')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])
            ->all();

        $tren = $this->trenBulanan($base(), $tahun);

        $terbaru = CutiPengajuan::with(['pegawai.opd', 'jenisCutiAturan'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $pegawaiUnik = $base()->distinct('pegawai_id')->count('pegawai_id');
        $opdAktifCount = MasterOpd::count();

        return [
            'rekap' => $rekap,
            'menungguFinal' => $menungguFinal,
            'jenisCuti' => $jenisCuti,
            'topOpd' => $topOpd,
            'tren' => $tren,
            'terbaru' => $terbaru,
            'pegawaiUnik' => $pegawaiUnik,
            'opdAktifCount' => $opdAktifCount,
            'tahun' => $tahun,
        ];
    }

    /**
     * @return array{diajukan: int, disetujui: int, ditolak: int, dibatalkan: int, total: int}
     */
    protected function rekapStatus(Builder $query): array
    {
        $rows = $query->selectRaw('status, COUNT(*) AS jumlah')->groupBy('status')->pluck('jumlah', 'status');

        $rekap = [
            'diajukan' => (int) ($rows['diajukan'] ?? 0),
            'disetujui' => (int) ($rows['disetujui'] ?? 0),
            'ditolak' => (int) ($rows['ditolak'] ?? 0),
            'dibatalkan' => (int) ($rows['dibatalkan'] ?? 0),
        ];
        $rekap['total'] = array_sum($rekap);

        return $rekap;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function distribusiJenisCuti(Builder $query): array
    {
        return $query
            ->join('jenis_cuti_aturan', 'jenis_cuti_aturan.id', '=', 'cuti_pengajuan.jenis_cuti_aturan_id')
            ->selectRaw('jenis_cuti_aturan.nama AS label, COUNT(*) AS value')
            ->groupBy('jenis_cuti_aturan.nama')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    protected function trenBulanan(Builder $query, int $tahun): array
    {
        $rows = $query->selectRaw('MONTH(tanggal_mulai) AS bulan, COUNT(*) AS jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'label' => Carbon::create($tahun, $m, 1)->translatedFormat('M'),
                'value' => (int) ($rows[$m] ?? 0),
            ];
        }

        return $result;
    }
}
