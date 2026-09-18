<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $table = 'sync_logs';

    protected $fillable = [
        'entity',
        'label',
        'status',
        'records_synced',
        'started_at',
        'finished_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Daftar entitas yang tersedia untuk disinkronkan, dipakai sebagai acuan
     * halaman Sync Data supaya entitas yang belum pernah disinkron tetap tampil.
     * Key mengikuti nama tabel sumber (mirror), lihat {@see \App\Services\SyncService}.
     *
     * @return array<string, string>
     */
    public static function entities(): array
    {
        return [
            'tkodecuti' => 'TKodeCuti (Jenis Cuti)',
            'kode_formasijabatan' => 'KODE_FORMASIJABATAN (Nama Jabatan)',
            'sysdb_instansi' => 'SYSDB_INSTANSI (OPD Induk)',
            'sysdb_wkguni' => 'SYSDB_WKGUNI (Unit Kerja)',
            'sysdb_wkgunitampungan' => 'SYSDB_WKGUNITAMPUNGAN',
            'tprofilunitkerja' => 'TProfilUnitKerja',
            'sysdb_pns' => 'SYSDB_PNS (Data Pegawai)',
            'sysdb_pnsdetail' => 'SYSDB_PNSDETAIL',
            'sysdb_pnskpg' => 'SYSDB_PNSKPG',
            'riwayatcuti' => 'RiwayatCuti',
        ];
    }
}
