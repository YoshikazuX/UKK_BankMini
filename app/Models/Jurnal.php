<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'jurnal';

    protected $fillable = [
        'transaksi_id',
        'tanggal',
        'keterangan',
        'debit',
        'kredit',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'debit' => 'integer',
            'kredit' => 'integer',
        ];
    }

    /**
     * Relasi ke transaksi asal mutasi jurnal.
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
