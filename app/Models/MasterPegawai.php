<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MasterPegawai extends Model
{
    protected $table = 'tb_pegawai_aktif';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_nonaktif' => 'date',
        ];
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(MasterOpd::class, 'opd_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pegawai_id');
    }

    public function namaLengkap(): string
    {
        return trim(($this->gelar_depan ? $this->gelar_depan.' ' : '').$this->nama.($this->gelar_belakang ? ', '.$this->gelar_belakang : ''));
    }
}
