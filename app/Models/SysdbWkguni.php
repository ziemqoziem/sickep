<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysdbWkguni extends Model
{
    protected $table = 'sysdb_wkguni';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
