<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiPengajuanTahap extends Model
{
    protected $table = 'cuti_pengajuan_tahap';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'diputuskan_pada' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function cutiPengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyetuju_id');
    }
}
