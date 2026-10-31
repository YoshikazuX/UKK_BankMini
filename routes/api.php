<?php

use App\Http\Controllers\Api\AuthNasabahController;
use App\Http\Controllers\Api\AuthPetugasController;
use App\Http\Controllers\Api\AvalisApiController;
use App\Http\Controllers\Api\LaporanApiController;
use App\Http\Controllers\Api\OperasionalKasApiController;
use App\Http\Controllers\Api\ProfilNasabahController;
use App\Http\Controllers\Api\SystemApiController;
use App\Http\Controllers\Api\TellerApiController;
use App\Http\Controllers\Api\TransaksiNasabahController;
use App\Http\Controllers\Api\UniversalAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistem Bank Mini Sekolah
|--------------------------------------------------------------------------
| Seluruh endpoint REST API untuk:
| 1. Mobile Banking Nasabah / Siswa
| 2. Mobile / POS Petugas Loket Teller
| 3. Presensi & Jadwal Petugas Avalis
| 4. Laporan & Eksekutif Dashboard
| 5. System Health Check & Dokumentasi
*/

// =========================================================================
// 1. SISTEM, HEALTH CHECK & DOKUMENTASI API
// =========================================================================
Route::get('/', [SystemApiController::class, 'index'])->name('api.index');
Route::get('/health', [SystemApiController::class, 'health'])->name('api.health');
Route::get('/docs', [SystemApiController::class, 'docs'])->name('api.docs');

// =========================================================================
// 1B. AUTENTIKASI UNIVERSAL (ROLE DITENTUKAN OTOMATIS OLEH DATABASE)
// =========================================================================
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', [UniversalAuthController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [UniversalAuthController::class, 'me'])->name('me');
        Route::post('/logout', [UniversalAuthController::class, 'logout'])->name('logout');
    });
});

// =========================================================================
// 2. MODUL NASABAH (MOBILE BANKING SISWA)
// =========================================================================
Route::prefix('nasabah')->name('api.nasabah.')->group(function () {
    // Autentikasi & Akun Siswa
    Route::post('/login', [AuthNasabahController::class, 'login'])->name('login');
    Route::post('/logout', [AuthNasabahController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('/me', [AuthNasabahController::class, 'me'])->name('me');
    Route::get('/profil', [AuthNasabahController::class, 'me'])->name('profil');
    Route::post('/update-profil', [AuthNasabahController::class, 'updateProfil'])->name('update-profil');

    // Keamanan PIN & Password
    Route::post('/verify-pin', [AuthNasabahController::class, 'verifyPin'])->name('verify-pin');
    Route::post('/ganti-password', [AuthNasabahController::class, 'gantiPassword'])->name('ganti-password');
    Route::post('/ganti-pin', [AuthNasabahController::class, 'gantiPin'])->name('ganti-pin');

    // Inquiry & Saldo
    Route::get('/saldo', [ProfilNasabahController::class, 'saldo'])->name('saldo');
    Route::get('/cek-rekening', [ProfilNasabahController::class, 'cekRekening'])->name('cek-rekening');
    Route::get('/qris-payload', [ProfilNasabahController::class, 'qrisPayload'])->name('qris-payload');

    // Mutasi & Transaksi Mobile (Transfer antar siswa dinonaktifkan: tabungan dikelola loket teller)
    Route::get('/riwayat', [TransaksiNasabahController::class, 'riwayat'])->name('riwayat');
    Route::get('/ringkasan', [TransaksiNasabahController::class, 'ringkasan'])->name('ringkasan');
});

// =========================================================================
// 3. MODUL PETUGAS & LOKET TELLER (MOBILE POS & STAFF)
// =========================================================================
Route::prefix('petugas')->name('api.petugas.')->group(function () {
    // Autentikasi Petugas
    Route::post('/login', [AuthPetugasController::class, 'login'])->name('login');

    // Layanan Loket Terotentikasi
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthPetugasController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthPetugasController::class, 'me'])->name('me');

        // Pencarian & Inquiry Nasabah Loket
        Route::get('/nasabah', [TellerApiController::class, 'searchNasabah'])->name('nasabah.search');
        Route::get('/nasabah/{identifier}', [TellerApiController::class, 'detailNasabah'])->name('nasabah.detail');

        // Transaksi Finansial Loket
        Route::post('/transaksi/setor', [TellerApiController::class, 'setor'])->name('transaksi.setor');
        Route::post('/transaksi/tarik', [TellerApiController::class, 'tarik'])->name('transaksi.tarik');
        Route::get('/transaksi/status-idempotency/{key}', [TellerApiController::class, 'statusIdempotency'])->name('transaksi.status-idempotency');
        Route::get('/transaksi/hari-ini', [TellerApiController::class, 'transaksiHariIni'])->name('transaksi.hari-ini');

        // Posisi Kas Teller
        Route::get('/kas', [TellerApiController::class, 'statusKas'])->name('kas');

        // Droping Kas & Daily Report (Teller Loket)
        Route::get('/pengajuan-kas', [OperasionalKasApiController::class, 'listPengajuanKasTeller'])->name('pengajuan-kas');
        Route::post('/pengajuan-kas', [OperasionalKasApiController::class, 'storePengajuanKasTeller'])->name('pengajuan-kas.store');
        Route::get('/daily-report', [OperasionalKasApiController::class, 'statusDailyReportTeller'])->name('daily-report');
        Route::post('/daily-report', [OperasionalKasApiController::class, 'storeDailyReportTeller'])->name('daily-report.store');
    });
});

// =========================================================================
// 4. MODUL SUPERVISOR (APPROVAL DROPING KAS & VERIFIKASI DAILY REPORT)
// =========================================================================
Route::prefix('supervisor')->name('api.supervisor.')->middleware('auth:sanctum')->group(function () {
    Route::get('/pengajuan-kas', [OperasionalKasApiController::class, 'listPengajuanKasSupervisor'])->name('pengajuan-kas');
    Route::post('/pengajuan-kas/{id}/setujui', [OperasionalKasApiController::class, 'setujuiPengajuanKas'])->name('pengajuan-kas.setujui');
    Route::post('/pengajuan-kas/{id}/tolak', [OperasionalKasApiController::class, 'tolakPengajuanKas'])->name('pengajuan-kas.tolak');
    Route::get('/daily-report', [OperasionalKasApiController::class, 'listDailyReportSupervisor'])->name('daily-report');
    Route::post('/daily-report/{id}/status', [OperasionalKasApiController::class, 'updateStatusDailyReport'])->name('daily-report.status');
});

// =========================================================================
// 5. MODUL AVALIS & PRESENSI LOKET
// =========================================================================
Route::prefix('avalis')->name('api.avalis.')->group(function () {
    // Absensi & Shift Petugas
    Route::get('/absensi', [AvalisApiController::class, 'listAbsensi'])->name('absensi');
    Route::get('/jadwal-hari-ini', [AvalisApiController::class, 'jadwalHariIni'])->name('jadwal-hari-ini');
    Route::post('/absensi/check-in', [AvalisApiController::class, 'checkIn'])->name('absensi.check-in');

    // Verifikasi Kunjungan Siswa ke Loket
    Route::get('/kunjungan-siswa', [AvalisApiController::class, 'kunjunganSiswa'])->name('kunjungan-siswa');
    Route::post('/kunjungan-siswa/verifikasi', [AvalisApiController::class, 'verifikasiKunjungan'])->name('kunjungan-siswa.verifikasi');
});

// =========================================================================
// 6. MODUL LAPORAN & EKSEKUTIF METRICS
// =========================================================================
Route::prefix('laporan')->name('api.laporan.')->group(function () {
    Route::get('/ringkasan-harian', [LaporanApiController::class, 'ringkasanHarian'])->name('ringkasan-harian');
    Route::get('/saldo-per-kelas', [LaporanApiController::class, 'saldoPerKelas'])->name('saldo-per-kelas');
    Route::get('/mutasi-jurnal', [LaporanApiController::class, 'mutasiJurnal'])->name('mutasi-jurnal');
});
