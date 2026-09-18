<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisCutiAturan extends Model
{
    protected $table = 'jenis_cuti_aturan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'perlu_dokumen' => 'boolean',
            'butuh_persetujuan_admin' => 'boolean',
            'aktif' => 'boolean',
        ];
    }
}
