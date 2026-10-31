<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $table = 'daily_reports';

    protected $fillable = [
        'user_id',
        'report_date',
        'opening_balance',
        'total_deposit',
        'total_cash_drop',
        'total_withdrawal',
        'system_balance',
        'physical_balance',
        'difference',
        'status',
        'approved_by',
        'notes',
        'catatan_spv',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'opening_balance' => 'integer',
            'total_deposit' => 'integer',
            'total_cash_drop' => 'integer',
            'total_withdrawal' => 'integer',
            'system_balance' => 'integer',
            'physical_balance' => 'integer',
            'difference' => 'integer',
        ];
    }

    /**
     * Petugas teller pembuat laporan harian.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Supervisor / Accounting yang menyetujui laporan harian.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Daftar transaksi loket yang terkait dengan laporan harian ini.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'daily_report_id');
    }

    /**
     * Riwayat jejak keputusan supervisor atas daily report ini.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'pekerjaan_id')
            ->where('pekerjaan_type', 'daily_report')
            ->orderBy('created_at', 'desc');
    }
}
