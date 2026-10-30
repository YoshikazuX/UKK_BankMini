<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemApiController extends Controller
{
    /**
     * Beranda Informasi API Bank Mini
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'app_name' => 'Bank Mini Sekolah API',
            'version' => '2.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'status' => 'operational',
            'server_time' => now()->toIso8601String(),
            'modules' => [
                'nasabah' => [
                    'prefix' => '/api/nasabah',
                    'description' => 'Layanan Mobile Banking Siswa (Login, Saldo, Transfer, PIN, QRIS, Mutasi)',
                ],
                'petugas' => [
                    'prefix' => '/api/petugas',
                    'description' => 'Layanan Mobile/POS Teller Loket (Login Petugas, Setor, Tarik, Search Nasabah, Posisi Kas)',
                ],
                'avalis' => [
                    'prefix' => '/api/avalis',
                    'description' => 'Layanan Avalis & Presensi (Jadwal Shift, Absensi Petugas, Verifikasi Kunjungan Siswa)',
                ],
                'laporan' => [
                    'prefix' => '/api/laporan',
                    'description' => 'Layanan Laporan & Eksekutif (Ringkasan Harian, Saldo per Kelas, Jurnal Akuntansi)',
                ],
                'system' => [
                    'prefix' => '/api',
                    'description' => 'Healthcheck (/api/health) dan Katalog Dokumentasi Endpoint (/api/docs)',
                ],
            ],
        ], 200);
    }

    /**
     * Endpoint Health Check Sistem & Database
     */
    public function health(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $dbStatus = 'disconnected';
        $dbLatency = 0;

        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
            $dbLatency = round((microtime(true) - $startTime) * 1000, 2);
        } catch (Exception $e) {
            $dbStatus = 'error: '.$e->getMessage();
        }

        $isHealthy = ($dbStatus === 'connected');

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'uptime' => 'active',
            'services' => [
                'database' => [
                    'status' => $dbStatus,
                    'latency_ms' => $dbLatency,
                ],
                'storage' => [
                    'public_disk_writable' => is_writable(storage_path('app/public')),
                ],
            ],
            'environment' => app()->environment(),
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Katalog Dokumentasi Seluruh Endpoint API
     */
    public function docs(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'title' => 'Katalog Dokumentasi API Bank Mini Sekolah',
            'version' => '2.0.0',
            'auth_methods' => [
                'bearer_token' => 'Gunakan header Authorization: Bearer <token> (diperoleh dari login)',
                'nasabah_pin' => 'Otorisasi 6-digit PIN untuk transaksi finansial (transfer/tarik)',
            ],
            'endpoints' => [
                'nasabah' => [
                    ['method' => 'POST', 'path' => '/api/nasabah/login', 'auth' => false, 'desc' => 'Login siswa / nasabah'],
                    ['method' => 'POST', 'path' => '/api/nasabah/logout', 'auth' => true, 'desc' => 'Logout & hapus token nasabah'],
                    ['method' => 'GET', 'path' => '/api/nasabah/me', 'auth' => true, 'desc' => 'Ambil profil lengkap nasabah yang sedang login'],
                    ['method' => 'POST', 'path' => '/api/nasabah/update-profil', 'auth' => true, 'desc' => 'Perbarui kontak no_hp & email nasabah'],
                    ['method' => 'GET', 'path' => '/api/nasabah/saldo', 'auth' => 'opsional', 'desc' => 'Inquiry saldo rekening tabungan'],
                    ['method' => 'GET', 'path' => '/api/nasabah/cek-rekening', 'auth' => false, 'desc' => 'Inquiry nama & rekening tujuan sebelum transfer'],
                    ['method' => 'GET', 'path' => '/api/nasabah/qris-payload', 'auth' => 'opsional', 'desc' => 'Payload data QR untuk scan loket'],
                    ['method' => 'POST', 'path' => '/api/nasabah/verify-pin', 'auth' => false, 'desc' => 'Verifikasi 6-digit PIN kartu nasabah'],
                    ['method' => 'POST', 'path' => '/api/nasabah/ganti-password', 'auth' => false, 'desc' => 'Ganti password akun nasabah'],
                    ['method' => 'POST', 'path' => '/api/nasabah/ganti-pin', 'auth' => false, 'desc' => 'Ganti 6-digit PIN keamanan nasabah'],
                    ['method' => 'POST', 'path' => '/api/nasabah/transfer', 'auth' => 'opsional', 'desc' => 'Transfer saldo antar rekening nasabah'],
                    ['method' => 'GET', 'path' => '/api/nasabah/riwayat', 'auth' => 'opsional', 'desc' => 'Riwayat mutasi transaksi nasabah'],
                    ['method' => 'GET', 'path' => '/api/nasabah/ringkasan', 'auth' => 'opsional', 'desc' => 'Ringkasan pemasukan & pengeluaran bulan berjalan'],
                ],
                'petugas' => [
                    ['method' => 'POST', 'path' => '/api/petugas/login', 'auth' => false, 'desc' => 'Login pegawai (Teller / Supervisor / Admin)'],
                    ['method' => 'POST', 'path' => '/api/petugas/logout', 'auth' => true, 'desc' => 'Logout petugas loket'],
                    ['method' => 'GET', 'path' => '/api/petugas/me', 'auth' => true, 'desc' => 'Profil petugas & info shift jaga hari ini'],
                    ['method' => 'GET', 'path' => '/api/petugas/nasabah', 'auth' => true, 'desc' => 'Cari nasabah by NIS / Nama / Rekening'],
                    ['method' => 'GET', 'path' => '/api/petugas/nasabah/{identifier}', 'auth' => true, 'desc' => 'Detail nasabah & mutasi terakhir'],
                    ['method' => 'POST', 'path' => '/api/petugas/transaksi/setor', 'auth' => true, 'desc' => 'Setoran tunai loket teller'],
                    ['method' => 'POST', 'path' => '/api/petugas/transaksi/tarik', 'auth' => true, 'desc' => 'Penarikan tunai loket teller'],
                    ['method' => 'GET', 'path' => '/api/petugas/transaksi/hari-ini', 'auth' => true, 'desc' => 'Daftar transaksi loket hari ini'],
                    ['method' => 'GET', 'path' => '/api/petugas/kas', 'auth' => true, 'desc' => 'Status sisa kas brankas & loket hari ini'],
                ],
                'avalis' => [
                    ['method' => 'GET', 'path' => '/api/avalis/absensi', 'auth' => false, 'desc' => 'Data presensi absensi petugas avalis'],
                    ['method' => 'GET', 'path' => '/api/avalis/jadwal-hari-ini', 'auth' => false, 'desc' => 'Jadwal shift jaga avalis (Pagi & Siang)'],
                    ['method' => 'POST', 'path' => '/api/avalis/absensi/check-in', 'auth' => 'opsional', 'desc' => 'Clock-in / Presensi loket avalis'],
                    ['method' => 'GET', 'path' => '/api/avalis/kunjungan-siswa', 'auth' => false, 'desc' => 'Monitoring status kunjungan siswa'],
                    ['method' => 'POST', 'path' => '/api/avalis/kunjungan-siswa/verifikasi', 'auth' => 'opsional', 'desc' => 'Verifikasi kehadiran siswa di loket'],
                ],
                'laporan' => [
                    ['method' => 'GET', 'path' => '/api/laporan/ringkasan-harian', 'auth' => false, 'desc' => 'Ringkasan eksekutif bank mini hari ini'],
                    ['method' => 'GET', 'path' => '/api/laporan/saldo-per-kelas', 'auth' => false, 'desc' => 'Agregasi tabungan per kelas/jurusan'],
                    ['method' => 'GET', 'path' => '/api/laporan/mutasi-jurnal', 'auth' => false, 'desc' => 'Buku jurnal akuntansi perbankan'],
                ],
                'system' => [
                    ['method' => 'GET', 'path' => '/api', 'auth' => false, 'desc' => 'Informasi umum API'],
                    ['method' => 'GET', 'path' => '/api/health', 'auth' => false, 'desc' => 'Health check konektivitas & status server'],
                    ['method' => 'GET', 'path' => '/api/docs', 'auth' => false, 'desc' => 'Katalog dokumentasi endpoint API'],
                ],
            ],
        ], 200);
    }
}
