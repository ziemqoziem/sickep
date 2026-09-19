<?php

namespace App\Observers;

use App\Models\MasterPegawai;

class MasterPegawaiObserver
{
    /**
     * Kalau pegawai (tb_pegawai_aktif) dinonaktifkan, akun login yang
     * tertaut (users.pegawai_id) ikut dinonaktifkan otomatis supaya tidak
     * bisa login lagi -- lihat LoginRequest::authenticate() &
     * EnsureUserIsActive. Arah sebaliknya (pegawai diaktifkan lagi)
     * sengaja TIDAK auto-mengaktifkan akun, karena Admin mungkin
     * menonaktifkan akun tsb secara independen untuk alasan lain.
     */
    public function updated(MasterPegawai $masterPegawai): void
    {
        if ($masterPegawai->wasChanged('status_aktif') && $masterPegawai->status_aktif === 'Tidak Aktif') {
            $masterPegawai->user?->update(['aktif' => false]);
        }
    }
}
