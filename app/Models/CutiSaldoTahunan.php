<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiSaldoTahunan extends Model
{
    protected $table = 'cuti_saldo_tahunan';

    protected $guarded = ['id'];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }
}
