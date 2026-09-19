<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kelas badge per HTTP method, dipakai di halaman Log Aktivitas.
     */
    public function methodWarna(): string
    {
        return match ($this->method) {
            'POST' => 'bg-emerald-50 text-emerald-700',
            'PUT', 'PATCH' => 'bg-amber-50 text-amber-700',
            'DELETE' => 'bg-red-50 text-red-700',
            default => 'bg-sky-50 text-sky-700',
        };
    }
}
