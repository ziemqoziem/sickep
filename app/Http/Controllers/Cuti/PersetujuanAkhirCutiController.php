<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\CutiPengajuanTahap;
use App\Services\CutiTahapKeputusanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * Modul BERDIRI SENDIRI untuk persetujuan akhir Cuti Besar & CLTN --
 * sengaja terpisah dari CutiPengajuanController/"Persetujuan Saya"
 * (requirement.md bagian 1, 8, 9). Akses cukup lewat middleware admin
 * biasa (bukan lewat CutiPengajuanTahapPolicy, yang tetap dipertahankan
 * untuk jenjang admin jika suatu saat modul lain butuh cek yang sama).
 */
class PersetujuanAkhirCutiController extends Controller
{
    protected const PER_PAGE = 10;

    public function __construct(protected CutiTahapKeputusanService $keputusanService) {}

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->query('page', 1));

        $query = CutiPengajuanTahap::query()
            ->with(['cutiPengajuan.pegawai.opd', 'cutiPengajuan.jenisCutiAturan', 'cutiPengajuan.tahap.penyetuju'])
            ->where('jenjang', 'admin')
            ->where('status', 'menunggu')
            ->whereHas('cutiPengajuan', function ($q) {
                // Validasi ganda: tahap urutan 1 & 2 pada pengajuan induk
                // harus sudah 'disetujui' sebelum boleh muncul di modul ini.
                $q->whereDoesntHave('tahap', function ($sub) {
                    $sub->whereIn('urutan', [1, 2])->where('status', '!=', 'disetujui');
                });
            });

        $total = (clone $query)->count();

        $tahapList = (clone $query)
            ->orderBy('created_at')
            ->forPage($page, static::PER_PAGE)
            ->get();

        $paginated = new LengthAwarePaginator(
            $tahapList,
            $total,
            static::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cuti-baru.persetujuan-akhir', ['tahapList' => $paginated]);
    }

    public function setujui(Request $request, CutiPengajuanTahap $cutiPengajuanTahap): RedirectResponse
    {
        $this->pastikanTahapAdminValid($cutiPengajuanTahap);

        $this->keputusanService->setujui($cutiPengajuanTahap, $request->user(), $request->input('catatan'));

        return redirect()->route('cuti-baru.persetujuan-akhir')
            ->with('status', "Pengajuan {$cutiPengajuanTahap->cutiPengajuan->nomor_pengajuan} berhasil disetujui (final).");
    }

    public function tolak(Request $request, CutiPengajuanTahap $cutiPengajuanTahap): RedirectResponse
    {
        $this->pastikanTahapAdminValid($cutiPengajuanTahap);

        $validated = $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $this->keputusanService->tolak($cutiPengajuanTahap, $request->user(), $validated['catatan']);

        return redirect()->route('cuti-baru.persetujuan-akhir')
            ->with('status', "Pengajuan {$cutiPengajuanTahap->cutiPengajuan->nomor_pengajuan} ditolak.");
    }

    protected function pastikanTahapAdminValid(CutiPengajuanTahap $tahap): void
    {
        abort_unless($tahap->jenjang === 'admin', 404);

        $belumLengkap = $tahap->cutiPengajuan->tahap()
            ->whereIn('urutan', [1, 2])
            ->where('status', '!=', 'disetujui')
            ->exists();

        abort_if($belumLengkap, 404, 'Jenjang Atasan Langsung dan Kepala Unit Kerja belum selesai.');
    }
}
