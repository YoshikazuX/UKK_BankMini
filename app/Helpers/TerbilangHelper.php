<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Helper rekursif internal untuk angka
     */
    private static function penyebut(float|int|string $angka): string
    {
        $angka = abs((float) $angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $hasil = '';

        if ($angka < 12) {
            $hasil = ' '.$baca[(int) $angka];
        } elseif ($angka < 20) {
            $hasil = self::penyebut($angka - 10).' Belas';
        } elseif ($angka < 100) {
            $hasil = self::penyebut((int) ($angka / 10)).' Puluh'.self::penyebut($angka % 10);
        } elseif ($angka < 200) {
            $hasil = ' Seratus'.self::penyebut($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = self::penyebut((int) ($angka / 100)).' Ratus'.self::penyebut($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = ' Seribu'.self::penyebut($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = self::penyebut((int) ($angka / 1000)).' Ribu'.self::penyebut($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = self::penyebut((int) ($angka / 1000000)).' Juta'.self::penyebut((int) $angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $hasil = self::penyebut((int) ($angka / 1000000000)).' Miliar'.self::penyebut(fmod($angka, 1000000000));
        } elseif ($angka < 1000000000000000) {
            $hasil = self::penyebut((int) ($angka / 1000000000000)).' Triliun'.self::penyebut(fmod($angka, 1000000000000));
        }

        return $hasil;
    }

    /**
     * Konversi Angka ke Kata Terbilang Rupiah
     * Contoh: 150000 -> "Seratus Lima Puluh Ribu Rupiah"
     */
    public static function terbilang(float|int|string $angka): string
    {
        if ((float) $angka === 0.0) {
            return 'Nol Rupiah';
        }

        $hasil = self::penyebut($angka);

        return trim(preg_replace('/\s+/', ' ', $hasil)).' Rupiah';
    }

    /**
     * Format Kode Rekening Armadillo:
     * (Kode Jurusan - No Urut 3 Digit)
     * Contoh: "Rekayasa Perangkat Lunak" -> RPL-001
     */
    public static function getKodeJurusan(?string $jurusanNama): string
    {
        $j = strtoupper(trim((string) $jurusanNama));

        if (str_contains($j, 'REKAYASA') || str_contains($j, 'RPL') || str_contains($j, 'PERANGKAT LUNAK')) {
            return 'RPL';
        } elseif (str_contains($j, 'JARINGAN') || str_contains($j, 'TKJ') || str_contains($j, 'KOMPUTER')) {
            return 'TKJ';
        } elseif (str_contains($j, 'AKUNTANSI') || str_contains($j, 'AKL') || str_contains($j, 'AK')) {
            return 'AK';
        } elseif (str_contains($j, 'RETAIL') || str_contains($j, 'RITEL') || str_contains($j, 'BR')) {
            return 'BR';
        } elseif (str_contains($j, 'BISNIS DIGITAL') || str_contains($j, 'BD')) {
            return 'BD';
        } elseif (str_contains($j, 'PERKANTORAN') || str_contains($j, 'MP') || str_contains($j, 'OTKP')) {
            return 'MP';
        } elseif (str_contains($j, 'KOMUNIKASI VISUAL') || str_contains($j, 'DKV')) {
            return 'DKV';
        } elseif (str_contains($j, 'GURU') || str_contains($j, 'KARYAWAN')) {
            return 'GK';
        }

        // Default ambil 3 huruf konsonan pertama atau prefix
        $clean = preg_replace('/[^A-Z]/', '', $j);

        return substr($clean, 0, 3) ?: 'TAB';
    }
}
