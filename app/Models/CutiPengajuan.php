<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutiPengajuan extends Model
{
    protected $table = 'cuti_pengajuan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_sk' => 'date',
            'pdf_generated_at' => 'datetime',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    public function atasanLangsungPegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'atasan_langsung_pegawai_id');
    }

    public function jenisCutiAturan(): BelongsTo
    {
        return $this->belongsTo(JenisCutiAturan::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function tahap(): HasMany
    {
        return $this->hasMany(CutiPengajuanTahap::class)->orderBy('urutan');
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(CutiPengajuanLampiran::class);
    }

    public function log(): HasMany
    {
        return $this->hasMany(CutiPengajuanLog::class)->latest('created_at');
    }

    /**
     * Baris tahap dengan urutan terkecil yang masih berstatus 'menunggu' --
     * giliran approve yang sedang aktif untuk pengajuan ini.
     */
    public function tahapAktif(): ?CutiPengajuanTahap
    {
        return $this->tahap->firstWhere('status', 'menunggu');
    }

    public function labelStatusJenjang(): string
    {
        if ($this->status !== 'diajukan') {
            return match ($this->status) {
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                'dibatalkan' => 'Dibatalkan',
                default => ucfirst($this->status),
            };
        }

        $aktif = $this->tahapAktif();

        return match ($aktif?->jenjang) {
            'atasan_langsung' => 'Menunggu Atasan Langsung',
            'kepala_unit_kerja' => 'Menunggu Kepala Unit Kerja',
            'admin' => 'Menunggu Admin',
            default => 'Diajukan',
        };
    }

    /**
     * Kelas badge (soft) + gradient (solid) untuk status keseluruhan --
     * dipakai di seluruh halaman Cuti Baru supaya warna status konsisten.
     */
    public function statusWarna(): array
    {
        return match ($this->status) {
            'disetujui' => ['badge' => 'bg-emerald-50 text-emerald-700', 'gradient' => 'from-emerald-500 to-emerald-600', 'dot' => 'bg-emerald-500'],
            'ditolak' => ['badge' => 'bg-red-50 text-red-700', 'gradient' => 'from-red-500 to-red-600', 'dot' => 'bg-red-500'],
            'dibatalkan' => ['badge' => 'bg-slate-100 text-slate-500', 'gradient' => 'from-slate-400 to-slate-500', 'dot' => 'bg-slate-400'],
            default => ['badge' => 'bg-amber-50 text-amber-700', 'gradient' => 'from-amber-500 to-amber-600', 'dot' => 'bg-amber-500'],
        };
    }
}
