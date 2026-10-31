<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'nama_petugas',
        'password',
        'role',
        'status',
        'kelas',
        'no_hp',
        'email',
        'foto',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Transaksi yang diproses oleh user / petugas ini.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'petugas_id');
    }

    /**
     * Daily Report yang dibuat oleh user / teller ini.
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'user_id');
    }

    /**
     * Pengajuan kas yang diajukan oleh user / teller ini.
     */
    public function pengajuanKas(): HasMany
    {
        return $this->hasMany(PengajuanKas::class, 'user_id');
    }

    /**
     * Profil data avalis yang terhubung dengan akun user ini.
     */
    public function avalis(): HasOne
    {
        return $this->hasOne(Avalis::class, 'user_id');
    }

    /**
     * Ambil URL foto profil user ini.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto && file_exists(public_path('storage/'.$this->foto))) {
            return asset('storage/'.$this->foto);
        }

        return null;
    }

    /**
     * Riwayat verifikasi kunjungan siswa yang dilakukan user ini.
     */
    public function verifikasiKunjungan(): HasMany
    {
        return $this->hasMany(VerifikasiKunjunganSiswa::class, 'user_id');
    }

    /**
     * Scope query untuk hanya mengambil user yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Cek apakah user berstatus aktif.
     */
    public function isAktif(): bool
    {
        return ($this->status ?? 'aktif') === 'aktif';
    }
}
