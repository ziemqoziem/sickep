<?php

namespace App\Services;

use App\Models\MasterPegawai;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AtasanLangsungService
{
    /**
     * Kandidat Atasan Langsung untuk seorang pemohon (skema terbuka --
     * lihat requirement.md bagian 2 & 5.4): pejabat struktural di OPD yang
     * sama, BUKAN Kepala OPD, dan sudah punya akun login supaya bisa
     * memproses persetujuan jenjang 1.
     *
     * PENTING: deteksi Kepala OPD memakai pencocokan teks nip <->
     * tb_opd_aktif.nip_kepala, BUKAN kolom tb_opd_aktif.kepala_pegawai_id
     * (foreign key itu rusak/selalu null, lihat catatan di MasterOpd).
     */
    public function kandidat(MasterPegawai $pemohon): Collection
    {
        if (! $pemohon->opd_id) {
            return collect();
        }

        $nipKepalaList = DB::table('tb_opd_aktif')
            ->whereNotNull('nip_kepala')
            ->pluck('nip_kepala');

        return MasterPegawai::query()
            ->with('user')
            ->where('opd_id', $pemohon->opd_id)
            ->where('id', '!=', $pemohon->id)
            ->where('jenis_jabatan', 'Jabatan Struktural')
            ->whereNotIn('nip', $nipKepalaList)
            ->whereHas('user')
            ->orderBy('nama')
            ->get();
    }
}
