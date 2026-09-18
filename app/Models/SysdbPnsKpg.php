<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysdbPnsKpg extends Model
{
    protected $table = 'sysdb_pnskpg';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
