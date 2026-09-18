<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysdbInstansi extends Model
{
    protected $table = 'sysdb_instansi';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
