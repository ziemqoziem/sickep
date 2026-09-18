<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SysdbPns extends Model
{
    protected $table = 'sysdb_pns';

    protected $guarded = ['id'];

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PegawaiStatusLog::class, 'pns_pnsnip', 'pns_pnsnip')->latest();
    }

    public function mutasiLogs(): HasMany
    {
        return $this->hasMany(PegawaiMutasiLog::class, 'pns_pnsnip', 'pns_pnsnip')->latest();
    }

    protected function casts(): array
    {
        return [
            'pns_ccsefd' => 'datetime',
            'pns_inadte' => 'datetime',
            'pns_echefd' => 'datetime',
            'pns_crndte' => 'datetime',
            'pns_penefd' => 'datetime',
            'pns_creadt' => 'datetime',
            'pns_updtdt' => 'datetime',
            'pns_postdt' => 'datetime',
            'pns_tgllstr' => 'datetime',
            'pns_tgllfun' => 'datetime',
            'pns_proses' => 'datetime',
            'pns_tmtgaji' => 'datetime',
            'pns_tmttam' => 'datetime',
            'tgl_sk_tugas' => 'datetime',
            'updtuker' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }
}
