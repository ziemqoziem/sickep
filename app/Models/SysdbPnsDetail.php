<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysdbPnsDetail extends Model
{
    protected $table = 'sysdb_pnsdetail';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dtl_jargjtgl' => 'datetime',
            'updtuker' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }
}
