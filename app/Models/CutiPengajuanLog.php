<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiPengajuanLog extends Model
{
    protected $table = 'cuti_pengajuan_log';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function cutiPengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }

    public function oleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oleh');
    }
}
