<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\PegawaiMutasiLog;
use App\Models\PegawaiStatusLog;
use App\Models\SysdbInstansi;
use App\Models\SysdbPns;
use App\Models\SysdbWkguni;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PegawaiActionController extends Controller
{
    public function nonaktifkan(Request $request, string $nip): RedirectResponse
    {
        $pegawai = SysdbPns::where('pns_pnsnip', $nip)->firstOrFail();

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
            'tanggal_efektif' => ['required', 'date'],
        ]);

        $pegawai->update([
            'status_aktif' => 'Tidak Aktif',
            'manual_override_status' => true,
        ]);

        PegawaiStatusLog::create([
            'pns_pnsnip' => $pegawai->pns_pnsnip,
            'nip_baru' => $pegawai->nip_baru,
            'nama' => $pegawai->pns_pnsnam,
            'status_baru' => 'Tidak Aktif',
            'alasan' => $validated['alasan'],
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('data.pegawai', $request->only(['q', 'status', 'page']))
            ->with('status', "{$pegawai->pns_pnsnam} berhasil dinonaktifkan.");
    }

    public function aktifkan(Request $request, string $nip): RedirectResponse
    {
        $pegawai = SysdbPns::where('pns_pnsnip', $nip)->firstOrFail();

        $validated = $request->validate([
            'alasan' => ['nullable', 'string', 'max:1000'],
            'tanggal_efektif' => ['required', 'date'],
        ]);

        $pegawai->update([
            'status_aktif' => 'Aktif',
            'manual_override_status' => true,
        ]);

        PegawaiStatusLog::create([
            'pns_pnsnip' => $pegawai->pns_pnsnip,
            'nip_baru' => $pegawai->nip_baru,
            'nama' => $pegawai->pns_pnsnam,
            'status_baru' => 'Aktif',
            'alasan' => $validated['alasan'] ?? null,
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('data.pegawai', $request->only(['q', 'status', 'page']))
            ->with('status', "{$pegawai->pns_pnsnam} berhasil diaktifkan kembali.");
    }

    public function mutasi(Request $request, string $nip): RedirectResponse
    {
        $pegawai = SysdbPns::where('pns_pnsnip', $nip)->firstOrFail();

        $validated = $request->validate([
            'opd_tujuan_kode' => ['required', 'string', 'exists:sysdb_instansi,ins_inscod'],
            'unit_kerja_tujuan_nama' => ['nullable', 'string', 'max:255'],
            'nomor_sk' => ['nullable', 'string', 'max:100'],
            'tanggal_sk' => ['nullable', 'date'],
            'tanggal_efektif' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $opdAsal = SysdbInstansi::where('ins_inscod', $pegawai->override_opd_kode ?? $pegawai->pns_inscod)->first();
        $opdTujuan = SysdbInstansi::where('ins_inscod', $validated['opd_tujuan_kode'])->firstOrFail();

        $unitKerjaAsalKode = $pegawai->override_unit_kerja_kode ?? $pegawai->pns_wkucod;
        $unitKerjaAsalNama = $pegawai->override_unit_kerja_nama
            ?? SysdbWkguni::where('wku_wrkcod', $unitKerjaAsalKode)->value('wku_name');

        PegawaiMutasiLog::create([
            'pns_pnsnip' => $pegawai->pns_pnsnip,
            'nip_baru' => $pegawai->nip_baru,
            'nama' => $pegawai->pns_pnsnam,
            'opd_asal_kode' => $opdAsal?->ins_inscod,
            'opd_asal_nama' => $opdAsal?->ins_insnam,
            'opd_tujuan_kode' => $opdTujuan->ins_inscod,
            'opd_tujuan_nama' => $opdTujuan->ins_insnam,
            'unit_kerja_asal_kode' => $unitKerjaAsalKode,
            'unit_kerja_asal_nama' => $unitKerjaAsalNama,
            'unit_kerja_tujuan_kode' => null,
            'unit_kerja_tujuan_nama' => $validated['unit_kerja_tujuan_nama'] ?? null,
            'nomor_sk' => $validated['nomor_sk'] ?? null,
            'tanggal_sk' => $validated['tanggal_sk'] ?? null,
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $pegawai->update([
            'override_opd_kode' => $opdTujuan->ins_inscod,
            'override_unit_kerja_kode' => null,
            'override_unit_kerja_nama' => $validated['unit_kerja_tujuan_nama'] ?? null,
        ]);

        return redirect()
            ->route('data.pegawai', $request->only(['q', 'status', 'page']))
            ->with('status', "{$pegawai->pns_pnsnam} berhasil dimutasi ke {$opdTujuan->ins_insnam}.");
    }
}
