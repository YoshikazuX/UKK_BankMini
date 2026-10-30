<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiAvalis extends Model
{
    use HasFactory;

    protected $table = 'absensi_avalis';

    protected $fillable = [
        'tanggal',
        'nama',
        'kelas',
        'no_wa',
        'shift_jaga',
        'status',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Akun user sistem yang bertindak sebagai petugas ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nama', 'nama_petugas');
    }

    /**
     * Master profil avalis yang terhubung.
     */
    public function avalis(): BelongsTo
    {
        return $this->belongsTo(Avalis::class, 'nama', 'nama');
    }

    /**
     * Ambil URL foto profil petugas avalis (dari user atau avalis->user).
     */
    public function getFotoUrlAttribute(): ?string
    {
        $foto = $this->user?->foto ?? $this->avalis?->user?->foto;

        if ($foto && file_exists(public_path('storage/'.$foto))) {
            return asset('storage/'.$foto);
        }

        return null;
    }

    /**
     * Scope absensi pada tanggal tertentu (default hari ini).
     */
    public function scopeOnDate(Builder $query, ?string $date = null): Builder
    {
        return $query->whereDate('tanggal', $date ?: now()->toDateString());
    }

    /**
     * Scope absensi berstatus Hadir.
     */
    public function scopeHadir(Builder $query): Builder
    {
        return $query->where('status', 'Hadir');
    }
}
