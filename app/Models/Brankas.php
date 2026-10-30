<?php

namespace App\Models;

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
}
