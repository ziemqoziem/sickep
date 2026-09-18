<?php

namespace App\Services;

use App\Models\CutiSaldoTahunan;
use App\Models\MasterPegawai;

class CutiSaldoService
{
    /**
     * Ambil (atau buat) baris saldo cuti tahunan pegawai untuk tahun
     * tertentu. Carry-over dihitung dari sisa saldo tahun sebelumnya,
     * maksimal 6 hari (lihat ketentuan-cuti.md bagian 2).
     */
    public function pastikan(MasterPegawai $pegawai, int $tahun): CutiSaldoTahunan
    {
        $existing = CutiSaldoTahunan::where('pegawai_id', $pegawai->id)->where('tahun', $tahun)->first();

        if ($existing) {
            return $existing;
        }

        $tahunLalu = CutiSaldoTahunan::where('pegawai_id', $pegawai->id)->where('tahun', $tahun - 1)->first();
        $carryOver = $tahunLalu ? min(6, max(0, $tahunLalu->sisa)) : 0;

        $jatah = 12;

        return CutiSaldoTahunan::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => $tahun,
            'jatah' => $jatah,
            'carry_over_masuk' => $carryOver,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
            'sisa' => $jatah + $carryOver,
        ]);
    }

    public function kurangi(MasterPegawai $pegawai, int $tahun, int $hari): CutiSaldoTahunan
    {
        $saldo = $this->pastikan($pegawai, $tahun);

        $saldo->terpakai += $hari;
        $saldo->sisa = $saldo->jatah + $saldo->carry_over_masuk + $saldo->tambahan_cuti_bersama - $saldo->terpakai;
        $saldo->save();

        $pegawai->update(['sisa_cuti_tahunan' => $saldo->sisa]);

        return $saldo;
    }
}
