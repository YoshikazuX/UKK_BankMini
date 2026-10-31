<?php

namespace App\Helpers;

use App\Models\AuditLog;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditHelper
{
    /**
     * Daftar kunci sensitif yang tidak boleh dicatat dalam nilai audit.
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'pin',
        'pin_keamanan',
        'pin_lama',
        'pin_baru',
        'password_lama',
        'password_baru',
        'remember_token',
        'token',
        'otp_code',
        'otp',
        'secret',
        'access_token',
        'bearer_token',
    ];

    /**
     * Catat log aktivitas ke tabel audit_logs.
     */
    public static function log(
        string $jenisAktivitas,
        string $modul,
        string|int|null $objekId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): ?AuditLog {
        try {
            $currentUser = $user ?? Auth::user();

            $userId = $currentUser?->id;
            $namaUser = $currentUser?->nama_petugas ?? ($currentUser?->nama_siswa ?? 'Sistem');
            $roleUser = $currentUser?->role ?? ($currentUser instanceof Nasabah ? 'nasabah' : 'sistem');

            $cleanOld = $oldValues !== null ? self::sanitize($oldValues) : null;
            $cleanNew = $newValues !== null ? self::sanitize($newValues) : null;

            return AuditLog::create([
                'user_id' => $userId,
                'nama_user' => $namaUser,
                'role_user' => $roleUser,
                'jenis_aktivitas' => $jenisAktivitas,
                'modul' => $modul,
                'objek_id' => $objekId !== null ? (string) $objekId : null,
                'old_values' => $cleanOld,
                'new_values' => $cleanNew,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => substr((string) Request::userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Sensor atau hapus kredensial sensitif dari payload data.
     */
    public static function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            $isSensitive = false;
            foreach (self::$sensitiveKeys as $sensitive) {
                if ($lowerKey === $sensitive || str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[DIHAPUS / TERLINDUNGI]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
