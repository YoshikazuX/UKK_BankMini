<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->nomor_pengajuan)) {
                $today = $model->tanggal ? Carbon::parse($model->tanggal)->format('Ymd') : date('Ymd');
                $count = static::whereDate('tanggal', $model->tanggal ?? today())->count() + 1;
                $model->nomor_pengajuan = 'PK-'.$today.'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->tanggal)) {
                $model->tanggal = now()->toDateString();
            }
        });
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

    /**
     * Riwayat jejak keputusan supervisor atas pengajuan kas ini.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'pekerjaan_id')
            ->where('pekerjaan_type', 'pengajuan_kas')
            ->orderBy('created_at', 'desc');
    }
}
