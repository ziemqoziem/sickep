<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncRun extends Model
{
    protected $table = 'sync_runs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_watermark' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }
}
