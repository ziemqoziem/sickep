<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'no_hp',
        'photo',
        'password',
        'role',
        'aktif',
        'pegawai_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOpd(): bool
    {
        return $this->role === 'opd';
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    public function opdDiampu(): BelongsToMany
    {
        return $this->belongsToMany(MasterOpd::class, 'opd_admins', 'user_id', 'opd_id');
    }

    public function photoUrl(): string
    {
        return $this->photo
            ? asset('storage/'.$this->photo)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=0369a1&background=e0f2fe';
    }

    /**
     * Palet gradasi warna per tipe peran, dipakai di halaman Master
     * Pengguna supaya admin/opd/user gampang dibedakan sekilas.
     */
    public function roleWarna(): array
    {
        return match ($this->role) {
            'admin' => ['from' => 'from-sky-500', 'to' => 'to-indigo-600', 'label' => 'Admin'],
            'opd' => ['from' => 'from-violet-500', 'to' => 'to-purple-600', 'label' => 'OPD'],
            default => ['from' => 'from-teal-500', 'to' => 'to-emerald-600', 'label' => 'User'],
        };
    }
}
