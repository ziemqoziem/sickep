<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterPegawaiStatusLog extends Model
{
    protected $table = 'master_pegawai_status_logs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_efektif' => 'date',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
