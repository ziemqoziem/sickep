<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterPegawaiMutasiLog extends Model
{
    protected $table = 'master_pegawai_mutasi_logs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tanggal_efektif' => 'date',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    public function opdAsal(): BelongsTo
    {
        return $this->belongsTo(MasterOpd::class, 'opd_asal_id');
    }

    public function opdTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterOpd::class, 'opd_tujuan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
