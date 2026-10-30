<?php

namespace App\Helpers;

use App\Models\PengaturanSistem;
use Illuminate\Support\Facades\Schema;

class SettingHelper
{
    /**
     * Ambil record pengaturan sistem global atau inisialisasi default jika belum ada.
     */
    public static function getSettings(): PengaturanSistem
    {
        if (! Schema::hasTable('pengaturan_sistem')) {
            return new PengaturanSistem([
                'saldo_mengendap_min' => 10000,
                'scanner_kamera_aktif' => true,
                'tahun_ajaran_aktif' => '2026/2027',
                'format_no_rekening' => 'TA-JURUSAN-URUT',
                'nama_bank' => 'LKM Mitra Siswa Abadi',
                'biaya_administrasi_buku' => 5000,
            ]);
        }

        return PengaturanSistem::firstOrCreate([], [
            'saldo_mengendap_min' => 10000,
            'scanner_kamera_aktif' => true,
            'tahun_ajaran_aktif' => '2026/2027',
            'format_no_rekening' => 'TA-JURUSAN-URUT',
            'nama_bank' => 'LKM Mitra Siswa Abadi',
            'biaya_administrasi_buku' => 5000,
        ]);
    }

    public static function getSaldoMengendapMin(): int
    {
        return (int) (self::getSettings()->saldo_mengendap_min ?? 10000);
    }

    public static function getTahunAjaran(): string
    {
        return self::getSettings()->tahun_ajaran_aktif ?? '2026/2027';
    }

    public static function isScannerKameraAktif(): bool
    {
        return (bool) (self::getSettings()->scanner_kamera_aktif ?? true);
    }
}
