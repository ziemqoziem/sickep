<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TKodeCuti extends Model
{
    protected $table = 'tkodecuti';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
