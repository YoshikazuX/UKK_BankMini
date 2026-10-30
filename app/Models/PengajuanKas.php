<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanKas extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_kas';

    protected $fillable = [
        'nomor_pengajuan',
        'user_id',
        'tanggal',
        'nominal',
        'alasan',
        'status',
        'disetujui_oleh',
        'catatan_spv',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
        ];
    }

    /**
     * Petugas teller yang mengajukan penambahan kas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Supervisor / Accounting yang memverifikasi & menyetujui pengajuan kas.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
