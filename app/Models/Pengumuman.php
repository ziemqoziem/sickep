<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Pengumuman yang sedang dalam rentang tanggal aktif hari ini --
     * satu-satunya kriteria yang dipakai untuk menentukan pop up mana
     * yang tampil setelah login.
     */
    public function scopeAktifHariIni(Builder $query): Builder
    {
        $hariIni = now()->toDateString();

        return $query->where('aktif', true)
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni);
    }

    public function statusRentang(): string
    {
        $hariIni = now()->toDateString();

        if (! $this->aktif) {
            return 'nonaktif';
        }

        if ($hariIni < $this->tanggal_mulai->toDateString()) {
            return 'terjadwal';
        }

        if ($hariIni > $this->tanggal_selesai->toDateString()) {
            return 'berakhir';
        }

        return 'berjalan';
    }
}
