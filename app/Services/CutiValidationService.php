<?php

namespace App\Services;

use App\Models\CutiPengajuan;
use App\Models\CutiSaldoTahunan;
use App\Models\JenisCutiAturan;
use App\Models\MasterPegawai;
use Carbon\Carbon;

class CutiValidationService
{
    /**
     * @return array<int, string> daftar pesan error; kosong berarti lolos validasi.
     */
    public function validate(MasterPegawai $pegawai, JenisCutiAturan $jenis, Carbon $mulai, ?Carbon $selesai): array
    {
        $errors = [];

        // Syarat masa kerja: tb_pegawai_aktif TIDAK punya kolom tanggal mulai
        // kerja/TMT (hasil sinkronisasi SIMABSARA2017 tidak menyertakannya).
        // Jadi syarat ini untuk sementara TIDAK divalidasi otomatis -- dicatat
        // sebagai temuan di requirement.md/develop.md, perlu keputusan lebih
        // lanjut (tambah kolom TMT, atau validasi manual oleh penyetuju).
        if ($jenis->syarat_masa_kerja_bulan) {
            // Sengaja tidak menolak pengajuan hanya karena data TMT belum ada.
        }

        if ($selesai && $selesai->lt($mulai)) {
            $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';

            return $errors;
        }

        $akhirRentang = $selesai ?? $mulai;

        // Tidak boleh tumpang tindih dengan pengajuan lain yang masih aktif.
        $tumpangTindih = CutiPengajuan::query()
            ->where('pegawai_id', $pegawai->id)
            ->whereIn('status', ['diajukan', 'disetujui'])
            ->where(function ($query) use ($mulai, $akhirRentang) {
                $query->where('tanggal_mulai', '<=', $akhirRentang->toDateString())
                    ->where(function ($sub) use ($mulai) {
                        $sub->whereNull('tanggal_selesai')
                            ->orWhere('tanggal_selesai', '>=', $mulai->toDateString());
                    });
            })
            ->exists();

        if ($tumpangTindih) {
            $errors[] = 'Rentang tanggal ini tumpang tindih dengan pengajuan cuti lain yang masih diajukan/disetujui.';
        }

        // Aturan silang: sedang Cuti Besar tahun ini -> tidak boleh Cuti Tahunan tahun sama.
        if ($jenis->kode === 'TAHUNAN') {
            $sedangCutiBesar = CutiPengajuan::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('status', 'disetujui')
                ->whereHas('jenisCutiAturan', fn ($q) => $q->where('kode', 'BESAR'))
                ->whereYear('tanggal_mulai', $mulai->year)
                ->exists();

            if ($sedangCutiBesar) {
                $errors[] = 'Pegawai ini sudah memakai Cuti Besar pada tahun yang sama, sehingga tidak berhak Cuti Tahunan di tahun tersebut.';
            }
        }

        // Aturan silang: sedang CLTN -> tidak boleh mengajukan jenis cuti lain.
        if ($jenis->kode !== 'CLTN') {
            $sedangCltn = CutiPengajuan::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('status', 'disetujui')
                ->whereHas('jenisCutiAturan', fn ($q) => $q->where('kode', 'CLTN'))
                ->where('tanggal_mulai', '<=', $akhirRentang->toDateString())
                ->where(function ($sub) use ($mulai) {
                    $sub->whereNull('tanggal_selesai')
                        ->orWhere('tanggal_selesai', '>=', $mulai->toDateString());
                })
                ->exists();

            if ($sedangCltn) {
                $errors[] = 'Pegawai ini sedang menjalani Cuti di Luar Tanggungan Negara, tidak bisa mengajukan jenis cuti lain.';
            }
        }

        // Validasi plafon/jatah hari.
        $lamaHari = $mulai->diffInDays($akhirRentang) + 1;

        if ($jenis->kode === 'TAHUNAN') {
            $saldo = CutiSaldoTahunan::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('tahun', $mulai->year)
                ->first();

            $sisa = $saldo->sisa ?? $jenis->jatah_hari ?? 12;

            if ($lamaHari > $sisa) {
                $errors[] = "Sisa Cuti Tahunan tahun {$mulai->year} tidak cukup (sisa {$sisa} hari, diajukan {$lamaHari} hari).";
            }
        } elseif ($jenis->maks_hari) {
            $terpakai = CutiPengajuan::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_aturan_id', $jenis->id)
                ->where('status', 'disetujui')
                ->sum('lama_hari');

            if (($terpakai + $lamaHari) > $jenis->maks_hari) {
                $errors[] = "Total {$jenis->nama} akan melebihi plafon {$jenis->maks_hari} hari (terpakai {$terpakai} hari, diajukan {$lamaHari} hari).";
            }
        }

        return $errors;
    }
}
