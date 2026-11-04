<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinResetRequest extends Model
{
    use HasFactory;

    protected $table = 'pin_reset_requests';

    protected $fillable = [
        'nasabah_id',
        'no_rekening',
        'nis',
        'nama_siswa',
        'alasan',
        'status',
        'approved_by',
        'reset_token',
        'approved_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id')->withTrashed();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
