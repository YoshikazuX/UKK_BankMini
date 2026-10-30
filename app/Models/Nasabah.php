<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Nasabah extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nasabah';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'no_rekening',
        'nis',
        'nama_siswa',
        'kelas',
        'jurusan',
        'username',
        'email',
        'no_hp',
        'password',
        'pin_keamanan',
        'saldo',
        'status',
        'kategori',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin_keamanan',
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
            'saldo' => 'integer',
        ];
    }

    /**
     * Scope nasabah aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Relasi ke data transaksi nasabah.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'nasabah_id');
    }

    /**
     * Relasi ke akun aplikasi mobile nasabah (user_app).
     */
    public function userApp(): HasOne
    {
        return $this->hasOne(UserApp::class, 'id_nasabah');
    }

    /**
     * Relasi ke catatan verifikasi kunjungan siswa.
     */
    public function verifikasiKunjungan(): HasMany
    {
        return $this->hasMany(VerifikasiKunjunganSiswa::class, 'nasabah_id');
    }
}
