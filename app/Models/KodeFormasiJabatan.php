<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeFormasiJabatan extends Model
{
    protected $table = 'kode_formasijabatan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
