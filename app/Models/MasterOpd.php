<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterOpd extends Model
{
    protected $table = 'tb_opd_aktif';

    protected $guarded = ['id'];

    public function kepalaPegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'kepala_pegawai_id');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(MasterPegawai::class, 'opd_id');
    }
}
