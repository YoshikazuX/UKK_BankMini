<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiKunjunganSiswa extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_kunjungan_siswa';

    protected $fillable = [
        'nasabah_id',
        'tanggal',
        'status_kunjungan',
        'waktu_verifikasi',
        'user_id',
        'nama_verifikator',
        'role_verifikator',
        'loket',
        'shift',
        'petugas_avalis_id',
        'petugas_avalis_nama',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu_verifikasi' => 'datetime',
        ];
    }

    /**
     * Data siswa / nasabah yang diverifikasi kunjungannya.
     */
    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    /**
     * Petugas (Teller / Admin) yang memverifikasi kehadiran.
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Petugas Avalis yang bertugas mendampingi di loket.
     */
    public function petugasAvalis(): BelongsTo
    {
        return $this->belongsTo(Avalis::class, 'petugas_avalis_id');
    }

    /**
     * Accessor alias nama_petugas -> nama_verifikator
     */
    public function getNamaPetugasAttribute(): ?string
    {
        return $this->nama_verifikator;
    }

    /**
     * Accessor alias catatan -> keterangan
     */
    public function getCatatanAttribute(): ?string
    {
        return $this->keterangan;
    }
}
