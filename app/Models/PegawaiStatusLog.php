<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiStatusLog extends Model
{
    protected $table = 'pegawai_status_logs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_efektif' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
