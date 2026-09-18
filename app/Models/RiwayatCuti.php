<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatCuti extends Model
{
    protected $table = 'riwayatcuti';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggalskcltn' => 'datetime',
            'tanggalawalcltn' => 'date',
            'tanggalakhircltn' => 'date',
            'tanggalaktifcltn' => 'datetime',
            'tanggalbkn' => 'datetime',
            'tglproses' => 'datetime',
            'tanggalpengantar' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }
}
