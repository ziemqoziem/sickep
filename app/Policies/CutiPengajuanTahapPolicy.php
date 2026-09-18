<?php

namespace App\Policies;

use App\Models\CutiPengajuanTahap;
use App\Models\User;

class CutiPengajuanTahapPolicy
{
    /**
     * Aturan lengkap ada di requirement.md bagian 9. $tahap harus juga
     * jadi baris urutan terkecil berstatus 'menunggu' pada pengajuan
     * terkait -- dicek terpisah oleh pemanggil (query index masing-masing
     * modul sudah otomatis hanya mengambil baris aktif, jadi di sini
     * cukup cek kecocokan wewenang).
     */
    public function approve(User $user, CutiPengajuanTahap $tahap): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $pengajuan = $tahap->cutiPengajuan;

        return match ($tahap->jenjang) {
            'atasan_langsung' => $user->pegawai_id !== null
                && $user->pegawai_id === $pengajuan->atasan_langsung_pegawai_id,
            'kepala_unit_kerja' => $user->isOpd()
                && $user->opdDiampu()->where('tb_opd_aktif.id', $pengajuan->pegawai->opd_id)->exists(),
            'admin' => false, // jenjang admin ditangani modul terpisah, lihat PersetujuanAkhirCutiController
            default => false,
        };
    }
}
