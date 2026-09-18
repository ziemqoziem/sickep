<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysdbWkguniTampungan extends Model
{
    protected $table = 'sysdb_wkgunitampungan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
