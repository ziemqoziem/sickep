<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterOpd;
use App\Models\MasterPegawai;
use App\Models\MasterPegawaiMutasiLog;
use App\Models\MasterPegawaiStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MasterPegawaiActionController extends Controller
{
    public function nonaktifkan(Request $request, MasterPegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
            'tanggal_efektif' => ['required', 'date'],
        ]);

        $pegawai->update([
            'status_aktif' => 'Tidak Aktif',
            'tanggal_nonaktif' => $validated['tanggal_efektif'],
            'keterangan_nonaktif' => $validated['alasan'],
        ]);

        MasterPegawaiStatusLog::create([
            'pegawai_id' => $pegawai->id,
            'nip' => $pegawai->nip,
            'nama' => $pegawai->nama,
            'status_baru' => 'Tidak Aktif',
            'alasan' => $validated['alasan'],
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('master.pegawai', $request->only(['q', 'status']))
            ->with('status', "{$pegawai->nama} berhasil dinonaktifkan.");
    }

    public function aktifkan(Request $request, MasterPegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'alasan' => ['nullable', 'string', 'max:1000'],
            'tanggal_efektif' => ['required', 'date'],
        ]);

        $pegawai->update([
            'status_aktif' => 'Aktif',
            'tanggal_nonaktif' => null,
            'keterangan_nonaktif' => null,
        ]);

        MasterPegawaiStatusLog::create([
            'pegawai_id' => $pegawai->id,
            'nip' => $pegawai->nip,
            'nama' => $pegawai->nama,
            'status_baru' => 'Aktif',
            'alasan' => $validated['alasan'] ?? null,
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('master.pegawai', $request->only(['q', 'status']))
            ->with('status', "{$pegawai->nama} berhasil diaktifkan kembali.");
    }

    public function mutasi(Request $request, MasterPegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'opd_tujuan_id' => ['required', 'exists:tb_opd_aktif,id'],
            'unit_kerja_tujuan' => ['nullable', 'string', 'max:255'],
            'nomor_sk' => ['nullable', 'string', 'max:100'],
            'tanggal_sk' => ['nullable', 'date'],
            'tanggal_efektif' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $opdAsal = $pegawai->opd;
        $opdTujuan = MasterOpd::findOrFail($validated['opd_tujuan_id']);

        MasterPegawaiMutasiLog::create([
            'pegawai_id' => $pegawai->id,
            'nip' => $pegawai->nip,
            'nama' => $pegawai->nama,
            'opd_asal_id' => $opdAsal?->id,
            'opd_asal_nama' => $opdAsal?->uraiunor,
            'opd_tujuan_id' => $opdTujuan->id,
            'opd_tujuan_nama' => $opdTujuan->uraiunor,
            'unit_kerja_asal' => $pegawai->unit_kerja,
            'unit_kerja_tujuan' => $validated['unit_kerja_tujuan'] ?? null,
            'nomor_sk' => $validated['nomor_sk'] ?? null,
            'tanggal_sk' => $validated['tanggal_sk'] ?? null,
            'tanggal_efektif' => $validated['tanggal_efektif'],
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $pegawai->update([
            'opd_id' => $opdTujuan->id,
            'unit_kerja' => $validated['unit_kerja_tujuan'] ?? $pegawai->unit_kerja,
        ]);

        return redirect()
            ->route('master.pegawai', $request->only(['q', 'status']))
            ->with('status', "{$pegawai->nama} berhasil dimutasi ke {$opdTujuan->uraiunor}.");
    }
}
