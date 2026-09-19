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

    /**
     * Palet warna per jenis cuti, dipakai di seluruh halaman "Cuti Baru"
     * (kartu, badge, ikon) supaya konsisten tanpa duplikasi logika warna
     * di tiap view.
     */
    public function warna(): array
    {
        return match ($this->kode) {
            'TAHUNAN' => ['from' => 'from-sky-500', 'to' => 'to-sky-600', 'soft' => 'bg-sky-50 text-sky-700', 'ring' => 'ring-sky-100'],
            'BESAR' => ['from' => 'from-violet-500', 'to' => 'to-violet-600', 'soft' => 'bg-violet-50 text-violet-700', 'ring' => 'ring-violet-100'],
            'SAKIT' => ['from' => 'from-rose-500', 'to' => 'to-rose-600', 'soft' => 'bg-rose-50 text-rose-700', 'ring' => 'ring-rose-100'],
            'MELAHIRKAN' => ['from' => 'from-pink-500', 'to' => 'to-pink-600', 'soft' => 'bg-pink-50 text-pink-700', 'ring' => 'ring-pink-100'],
            'ALASAN_PENTING' => ['from' => 'from-amber-500', 'to' => 'to-amber-600', 'soft' => 'bg-amber-50 text-amber-700', 'ring' => 'ring-amber-100'],
            'BERSAMA' => ['from' => 'from-teal-500', 'to' => 'to-teal-600', 'soft' => 'bg-teal-50 text-teal-700', 'ring' => 'ring-teal-100'],
            'CLTN' => ['from' => 'from-indigo-500', 'to' => 'to-indigo-600', 'soft' => 'bg-indigo-50 text-indigo-700', 'ring' => 'ring-indigo-100'],
            default => ['from' => 'from-slate-500', 'to' => 'to-slate-600', 'soft' => 'bg-slate-100 text-slate-600', 'ring' => 'ring-slate-100'],
        };
    }

    public function ikonPath(): string
    {
        return match ($this->kode) {
            'TAHUNAN' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'BESAR' => 'M9 17v-2a4 4 0 014-4h4M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v4M9 17l3 3m0 0l3-3m-3 3V9',
            'SAKIT' => 'M19 14l-7 7m0 0l-7-7m7 7V3',
            'MELAHIRKAN' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'ALASAN_PENTING' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
            'BERSAMA' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0',
            'CLTN' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
            default => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2',
        };
    }
}
