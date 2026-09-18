<?php

namespace App\Services;

use App\Models\CutiPengajuanLog;
use App\Models\CutiPengajuanTahap;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CutiTahapKeputusanService
{
    public function __construct(protected CutiSaldoService $saldoService) {}

    /**
     * Setujui satu baris tahap. Kalau ini tahap terakhir (urutan ===
     * jumlah_tahap pada pengajuan induk), pengajuan otomatis berstatus
     * 'disetujui' secara keseluruhan -- update saldo (khusus Cuti
     * Tahunan) dan tandai untuk generate dokumen (lihat Fase 5.3, belum
     * dipasang di sini supaya Fase 4 tidak bergantung ke dependency PDF).
     */
    public function setujui(CutiPengajuanTahap $tahap, User $penyetuju, ?string $catatan = null): CutiPengajuanTahap
    {
        return DB::transaction(function () use ($tahap, $penyetuju, $catatan) {
            $tahap->update([
                'status' => 'disetujui',
                'penyetuju_id' => $penyetuju->id,
                'diputuskan_pada' => now(),
                'catatan' => $catatan,
            ]);

            $pengajuan = $tahap->cutiPengajuan;

            $tahapBerikutnya = $pengajuan->tahap()
                ->where('urutan', '>', $tahap->urutan)
                ->where('status', 'menunggu')
                ->exists();

            if (! $tahapBerikutnya) {
                $statusSebelum = $pengajuan->status;
                $pengajuan->update(['status' => 'disetujui']);

                CutiPengajuanLog::create([
                    'cuti_pengajuan_id' => $pengajuan->id,
                    'status_sebelum' => $statusSebelum,
                    'status_sesudah' => 'disetujui',
                    'oleh' => $penyetuju->id,
                    'catatan' => 'Seluruh jenjang persetujuan selesai.',
                ]);

                if ($pengajuan->jenisCutiAturan->kode === 'TAHUNAN') {
                    $this->saldoService->kurangi($pengajuan->pegawai, $pengajuan->tanggal_mulai->year, $pengajuan->lama_hari);
                }

                // Generate PDF + QR dipasang di Fase 5.3 (CutiDokumenService).
            }

            return $tahap->fresh();
        });
    }

    public function tolak(CutiPengajuanTahap $tahap, User $penyetuju, string $catatan): CutiPengajuanTahap
    {
        return DB::transaction(function () use ($tahap, $penyetuju, $catatan) {
            $tahap->update([
                'status' => 'ditolak',
                'penyetuju_id' => $penyetuju->id,
                'diputuskan_pada' => now(),
                'catatan' => $catatan,
            ]);

            $pengajuan = $tahap->cutiPengajuan;

            $pengajuan->tahap()->where('status', 'menunggu')->update(['status' => 'dilewati']);

            $statusSebelum = $pengajuan->status;
            $pengajuan->update(['status' => 'ditolak']);

            CutiPengajuanLog::create([
                'cuti_pengajuan_id' => $pengajuan->id,
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => 'ditolak',
                'oleh' => $penyetuju->id,
                'catatan' => $catatan,
            ]);

            return $tahap->fresh();
        });
    }
}
