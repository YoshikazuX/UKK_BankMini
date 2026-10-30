<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'saldo_mengendap_min',
        'scanner_kamera_aktif',
        'tahun_ajaran_aktif',
        'format_no_rekening',
        'nama_bank',
        'biaya_administrasi_buku',
    ];

    protected function casts(): array
    {
        return [
            'saldo_mengendap_min' => 'integer',
            'scanner_kamera_aktif' => 'boolean',
            'biaya_administrasi_buku' => 'integer',
        ];
    }
}
