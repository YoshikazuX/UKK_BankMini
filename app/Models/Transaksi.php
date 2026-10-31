<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'nasabah_id',
        'petugas_id',
        'daily_report_id',
        'nama_petugas',
        'jenis_layanan',
        'jenis_transaksi',
        'kode_sandi',
        'nominal',
        'keterangan',
        'status',
        'idempotency_key',
        'alasan_batal',
        'ref_transaksi_id',
        'dibatalkan_oleh',
        'dibatalkan_at',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'dibatalkan_at' => 'datetime',
        ];
    }

    /**
     * Nasabah pemilik rekening.
     */
    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    /**
     * Petugas (Teller) yang memproses transaksi.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Petugas (Teller / Supervisor) yang membatalkan transaksi.
     */
    public function petugasPembatal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    /**
     * Transaksi asli yang dibatalkan / direferensikan.
     */
    public function originalTransaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'ref_transaksi_id');
    }

    /**
     * Transaksi pembalik / koreksi dari transaksi ini.
     */
    public function reversalTransaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'ref_transaksi_id');
    }

    /**
     * Daily Report yang mencatat transaksi ini.
     */
    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    /**
     * Mutasi jurnal akuntansi yang terkait transaksi ini.
     */
    public function jurnal(): HasMany
    {
        return $this->hasMany(Jurnal::class, 'transaksi_id');
    }
}
