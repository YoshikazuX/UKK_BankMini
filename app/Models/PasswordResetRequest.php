<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    use HasFactory;

    protected $table = 'password_reset_requests';

    protected $fillable = [
        'user_id',
        'username',
        'nama_pemohon',
        'metode',
        'kontak',
        'otp_code',
        'otp_expires_at',
        'status',
        'keterangan',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Akun user pemohon reset password.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
