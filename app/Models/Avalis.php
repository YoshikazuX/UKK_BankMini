<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avalis extends Model
{
    use HasFactory;

    protected $table = 'avalis';

    protected $fillable = [
        'nama',
        'kelas',
        'no_wa',
        'user_id',
        'status',
    ];

    /**
     * Akun user/petugas yang terhubung dengan data avalis ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ambil URL foto profil petugas avalis ini.
     */
    public function getFotoUrlAttribute(): ?string
    {
        $foto = $this->user?->foto;

        if ($foto && file_exists(public_path('storage/'.$foto))) {
            return asset('storage/'.$foto);
        }

        return null;
    }

    /**
     * Riwayat verifikasi kunjungan siswa yang dilayani oleh petugas avalis ini.
     */
    public function verifikasiKunjungan(): HasMany
    {
        return $this->hasMany(VerifikasiKunjunganSiswa::class, 'petugas_avalis_id');
    }
}
