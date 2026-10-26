<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingHelper
{
    public static function getSettings()
    {
        if (!Schema::hasTable('pengaturan_sistem')) {
            return (object) [
                'saldo_mengendap_min' => 10000,
                'scanner_kamera_aktif' => true,
                'tahun_ajaran_aktif' => '2026/2027',
                'format_no_rekening' => 'TA-JURUSAN-URUT',
                'nama_bank' => 'LKM Mitra Siswa Abadi',
                'biaya_administrasi_buku' => 5000,
            ];
        }

        $setting = DB::table('pengaturan_sistem')->first();
        if (!$setting) {
            DB::table('pengaturan_sistem')->insert([
                'saldo_mengendap_min' => 10000,
                'scanner_kamera_aktif' => true,
                'tahun_ajaran_aktif' => '2026/2027',
                'format_no_rekening' => 'TA-JURUSAN-URUT',
                'nama_bank' => 'LKM Mitra Siswa Abadi',
                'biaya_administrasi_buku' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $setting = DB::table('pengaturan_sistem')->first();
        }

        return $setting;
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
