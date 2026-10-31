<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ApprovalHistory extends Model
{
    use HasFactory;

    protected $table = 'approval_histories';

    protected $fillable = [
        'pekerjaan_type',
        'pekerjaan_id',
        'nomor_referensi',
        'teller_id',
        'nama_teller',
        'supervisor_id',
        'nama_supervisor',
        'jenis_keputusan',
        'status_sebelum',
        'status_sesudah',
        'nominal',
        'catatan',
        'alasan_penolakan',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Supervisor yang mengambil keputusan.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Relasi ke Teller pemohon.
     */
    public function teller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    /**
     * Rekam histori persetujuan / penolakan secara terstruktur.
     */
    public static function record(
        string $pekerjaanType,
        int|string $pekerjaanId,
        string $nomorReferensi,
        ?int $tellerId,
        string $namaTeller,
        string $jenisKeputusan,
        string $statusSebelum,
        string $statusSesudah,
        ?int $nominal = null,
        ?string $catatan = null,
        ?string $alasanPenolakan = null,
        ?User $supervisor = null
    ): self {
        $spv = $supervisor ?? Auth::user();

        return self::create([
            'pekerjaan_type' => $pekerjaanType,
            'pekerjaan_id' => (int) $pekerjaanId,
            'nomor_referensi' => $nomorReferensi,
            'teller_id' => $tellerId,
            'nama_teller' => $namaTeller,
            'supervisor_id' => $spv?->id,
            'nama_supervisor' => $spv?->nama_petugas ?? 'Supervisor',
            'jenis_keputusan' => $jenisKeputusan,
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusSesudah,
            'nominal' => $nominal,
            'catatan' => $catatan,
            'alasan_penolakan' => $alasanPenolakan,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
        ]);
    }
}
