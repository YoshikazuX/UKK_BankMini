<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TellerController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\AbsensiAvalisController;
use App\Http\Controllers\BukuTabunganController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\AvalisPetugasController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\PengajuanKasController;

Route::get('/', function () { return redirect('/login'); });
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================================================
// RUTE LUPA PASSWORD & PEMULIHAN AKUN (EMAIL / WHATSAPP / TIKET ADMIN)
// =========================================================================
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password/request-otp', [AuthController::class, 'requestOtp'])->name('password.otp');
Route::post('/forgot-password/verify-and-reset', [AuthController::class, 'verifyAndResetPassword'])->name('password.reset');
Route::post('/forgot-password/ticket', [AuthController::class, 'submitResetTicket'])->name('password.ticket');
Route::get('/forgot-password/cancel-session', [AuthController::class, 'resetForgotPasswordSession'])->name('password.cancel');

// =========================================================================
// RUTE ADMIN & CUSTOMER SERVICE (MANAJEMEN REKENING NASABAH BARU)
// =========================================================================
Route::middleware(['auth', 'role:admin,customer_service'])->group(function () {
    Route::get('/admin/data-nasabah', [NasabahController::class, 'index']);
    Route::get('/admin/tambah-nasabah', [NasabahController::class, 'create']);
    Route::post('/admin/tambah-nasabah', [NasabahController::class, 'store']);
    Route::get('/admin/edit-nasabah/{id}', [NasabahController::class, 'edit']);
    Route::post('/admin/edit-nasabah/{id}', [NasabahController::class, 'update']);
    Route::get('/admin/hapus-nasabah/{id}', [NasabahController::class, 'destroy']);
    Route::post('/admin/import-nasabah', [NasabahController::class, 'importCsv']);
    Route::get('/admin/template-nasabah', [NasabahController::class, 'downloadTemplate']);
});

// =========================================================================
// RUTE KHUSUS ADMIN (PEGAWAI & AUDIT LOG)
// =========================================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/tambah-pegawai', [AdminController::class, 'create']);
    Route::post('/admin/tambah-pegawai', [AdminController::class, 'store']);
    Route::get('/admin/edit-pegawai/{id}', [AdminController::class, 'edit']);
    Route::post('/admin/edit-pegawai/{id}', [AdminController::class, 'update']);
    Route::get('/admin/hapus-pegawai/{id}', [AdminController::class, 'destroy']);

    Route::get('/admin/profil', [AdminController::class, 'profil']);
    Route::post('/admin/profil/update', [AdminController::class, 'updateProfil']);

    // FITUR: LOG AUDIT & EKSPOR CSV
    Route::get('/admin/audit', [AdminController::class, 'auditLog']);
    Route::get('/admin/audit/export', [AdminController::class, 'exportAuditCsv']);

    // FITUR: PERSETUJUAN RESET PASSWORD PEGAWAI
    Route::post('/admin/reset-password-request/{id}/approve', [AdminController::class, 'approveResetRequest']);
    Route::post('/admin/reset-password-request/{id}/reject', [AdminController::class, 'rejectResetRequest']);
});

// =========================================================================
// RUTE TELLER (TRANSAKSI, DAILY REPORT & KOREKSI)
// =========================================================================
Route::middleware(['auth', 'role:teller'])->group(function () {
    Route::get('/teller/dashboard', [TellerController::class, 'index']);
    Route::get('/teller/transaksi/{id}', [TellerController::class, 'formTransaksi']);
    Route::post('/teller/transaksi/proses', [TellerController::class, 'prosesTransaksi']);
    Route::get('/teller/riwayat', [TellerController::class, 'riwayat']);
    Route::get('/teller/scan', [TellerController::class, 'scanQr']);
    Route::get('/teller/cari-nis/{nis}', [TellerController::class, 'cariByNis']);
    Route::get('/teller/profil', [TellerController::class, 'profil']);
    Route::post('/teller/profil/update', [TellerController::class, 'updateProfil']);
    Route::post('/teller/transaksi/void/{id}', [TellerController::class, 'voidTransaksi']);

    // DAILY REPORT TUTUP KAS TELLER
    Route::get('/teller/daily-report', [DailyReportController::class, 'index']);
    Route::post('/teller/daily-report/simpan', [DailyReportController::class, 'store']);

    // PENGAJUAN KAS TELLER (SAAT KAS HABIS / MENIPIS)
    Route::get('/teller/pengajuan-kas', [PengajuanKasController::class, 'indexTeller']);
    Route::post('/teller/pengajuan-kas/kirim', [PengajuanKasController::class, 'store']);
});

// =========================================================================
// RUTE SUPERVISOR & ACCOUNTING (APPROVAL & PENGELOLA LAPORAN)
// =========================================================================
Route::middleware(['auth', 'role:supervisor,accounting'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index']);
    Route::get('/supervisor/laporan', [SupervisorController::class, 'laporan']);
    Route::get('/supervisor/approve/{id}', [SupervisorController::class, 'approve']);
    Route::get('/supervisor/cetak-harian', [SupervisorController::class, 'cetakHarian']);
    Route::get('/supervisor/profil', [SupervisorController::class, 'profil']);
    Route::post('/supervisor/profil/update', [SupervisorController::class, 'updateProfil']);

    // DAILY REPORT APPROVAL / HOLD
    Route::get('/supervisor/daily-report', [DailyReportController::class, 'index']);
    Route::post('/supervisor/daily-report/status/{id}', [DailyReportController::class, 'updateStatus']);

    // PENGAJUAN KAS APPROVAL (DROPING DANA OPERASIONAL LOKET)
    Route::get('/supervisor/pengajuan-kas', [PengajuanKasController::class, 'indexSupervisor']);
    Route::post('/supervisor/pengajuan-kas/setujui/{id}', [PengajuanKasController::class, 'setujui']);
    Route::post('/supervisor/pengajuan-kas/tolak/{id}', [PengajuanKasController::class, 'tolak']);

    // LAPORAN KEUANGAN BANK MINI
    Route::get('/laporan/jurnal', [LaporanKeuanganController::class, 'jurnalAkuntansi']);
    Route::get('/laporan/saldo-produk', [LaporanKeuanganController::class, 'saldoPerProduk']);
    Route::get('/laporan/tahunan', [LaporanKeuanganController::class, 'laporanTahunan']);
});

// PENGATURAN KAS AWAL HARI INI (DISET OLEH PETUGAS)
Route::middleware(['auth'])->group(function () {
    Route::post('/kas-awal/set', [PengajuanKasController::class, 'setKasAwal']);
});

// =========================================================================
// RUTE UMUM PETUGAS (ABSENSI AVALIS, BUKU TABUNGAN & CETAK DOKUMEN)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/absensi-avalis', [AbsensiAvalisController::class, 'index']);
    Route::get('/teller/absensi-avalis', [AbsensiAvalisController::class, 'index']);
    Route::get('/supervisor/absensi-avalis', [AbsensiAvalisController::class, 'index']);
    Route::get('/absensi-avalis', [AbsensiAvalisController::class, 'index']);

    Route::post('/absensi-avalis/simpan', [AbsensiAvalisController::class, 'store']);
    Route::post('/absensi-avalis/update/{id}', [AbsensiAvalisController::class, 'update']);
    Route::get('/absensi-avalis/hapus/{id}', [AbsensiAvalisController::class, 'destroy']);
    Route::get('/absensi-avalis/export', [AbsensiAvalisController::class, 'exportCsv']);

    // MASTER IDENTITAS PETUGAS TELLER / AVALIS
    Route::get('/avalis/petugas', [AvalisPetugasController::class, 'index']);
    Route::post('/avalis/petugas/simpan', [AvalisPetugasController::class, 'store']);
    Route::post('/avalis/petugas/bulk', [AvalisPetugasController::class, 'storeBulk']);
    Route::post('/avalis/petugas/sync/{id}', [AvalisPetugasController::class, 'syncKeAbsensi']);
    Route::get('/avalis/petugas/hapus/{id}', [AvalisPetugasController::class, 'destroy']);

    // RUTE BUKU TABUNGAN & SLIP TRANSAKSI RESMI
    Route::get('/buku-tabungan/{id}', [BukuTabunganController::class, 'show']);
    Route::get('/buku-tabungan/cetak/{id}', [BukuTabunganController::class, 'cetakBuku']);
    Route::get('/teller/slip-setor/{id}', [BukuTabunganController::class, 'cetakSlipSetor']);
    Route::get('/teller/slip-tarik/{id}', [BukuTabunganController::class, 'cetakSlipTarik']);
    Route::post('/admin/nasabah/ubah-status/{id}', [BukuTabunganController::class, 'ubahStatus']);

    // CETAK LEMBAR DAILY REPORT
    Route::get('/daily-report/cetak/{id}', [DailyReportController::class, 'cetak']);

    // PORTAL DASHBOARD NASABAH
    Route::get('/nasabah/dashboard', [NasabahController::class, 'dashboardNasabah']);
});

// HELPER DATABASE & STORAGE
Route::get('/fix-database-pegawai', function () { return 'OK'; });
Route::get('/fix-database-void', function () { return 'OK'; });
Route::get('/fix-foto', function () { Artisan::call('storage:link'); return 'OK'; });

