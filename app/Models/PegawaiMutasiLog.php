<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiMutasiLog extends Model
{
    protected $table = 'pegawai_mutasi_logs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tanggal_efektif' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
