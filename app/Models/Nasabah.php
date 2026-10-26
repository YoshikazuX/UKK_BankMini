<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * @var array<int, string>
     */
    protected $fillable = [
        'no_rekening',
        'nis',
        'nama_siswa',
        'kelas',
        'jurusan',
        'username',
        'password',
        'pin_keamanan',
        'saldo',
        'status',
        'kategori',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
     * Relasi ke data transaksi nasabah
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'nasabah_id');
    }

    /**
     * Relasi ke akun aplikasi mobile nasabah (user_app)
     */
    public function userApp()
    {
        return $this->hasOne(UserApp::class, 'id_nasabah');
    }
}
