<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brankas extends Model
{
    use HasFactory;

    protected $table = 'brankas';

    protected $fillable = [
        'tanggal',
        'saldo_awal',
        'masuk',
        'keluar',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'saldo_awal' => 'integer',
            'masuk' => 'integer',
            'keluar' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * Dapatkan record brankas kas fisik hari ini atau inisialisasi dari saldo penutupan terakhir.
     */
    public static function getOrCreateToday(?string $date = null, bool $lock = false): self
    {
        $tanggal = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        $query = self::whereDate('tanggal', $tanggal);
        if ($lock) {
            $query->lockForUpdate();
        }
        $brankas = $query->first();

        if (! $brankas) {
            $kemarin = self::whereDate('tanggal', '<', $tanggal)->orderBy('tanggal', 'desc')->first();
            $saldoAwal = $kemarin ? (int) $kemarin->total : 0;
            try {
                $brankas = self::create([
                    'tanggal' => $tanggal,
                    'saldo_awal' => $saldoAwal,
                    'masuk' => 0,
                    'keluar' => 0,
                    'total' => $saldoAwal,
                ]);
            } catch (\Throwable $e) {
                $brankas = self::whereDate('tanggal', $tanggal)->first();
            }
            if ($lock && $brankas) {
                $brankas = self::where('id', $brankas->id)->lockForUpdate()->first();
            }
        }

        return $brankas;
    }
}
