<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TProfilUnitKerja extends Model
{
    protected $table = 'tprofilunitkerja';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
