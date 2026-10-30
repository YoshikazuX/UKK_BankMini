<?php

use App\Http\Controllers\AbsensiAvalisController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvalisPetugasController;
use App\Http\Controllers\BukuTabunganController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\PengajuanKasController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\TellerController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

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
    Route::get('/admin/data-nasabah', [NasabahController::class, 'index'])->name('admin.nasabah.index');
    Route::get('/admin/tambah-nasabah', [NasabahController::class, 'create'])->name('admin.nasabah.create');
    Route::post('/admin/tambah-nasabah', [NasabahController::class, 'store'])->name('admin.nasabah.store');
    Route::get('/admin/edit-nasabah/{id}', [NasabahController::class, 'edit'])->name('admin.nasabah.edit');
    Route::post('/admin/edit-nasabah/{id}', [NasabahController::class, 'update'])->name('admin.nasabah.update');
    Route::get('/admin/hapus-nasabah/{id}', [NasabahController::class, 'destroy'])->name('admin.nasabah.destroy');
    Route::post('/admin/import-nasabah', [NasabahController::class, 'importCsv'])->name('admin.nasabah.import');
    Route::get('/admin/template-nasabah', [NasabahController::class, 'downloadTemplate'])->name('admin.nasabah.template');
});

// =========================================================================
// RUTE KHUSUS ADMIN (PEGAWAI & AUDIT LOG)
// =========================================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/tambah-pegawai', [AdminController::class, 'create'])->name('admin.pegawai.create');
    Route::post('/admin/tambah-pegawai', [AdminController::class, 'store'])->name('admin.pegawai.store');
    Route::get('/admin/edit-pegawai/{id}', [AdminController::class, 'edit'])->name('admin.pegawai.edit');
    Route::post('/admin/edit-pegawai/{id}', [AdminController::class, 'update'])->name('admin.pegawai.update');
    Route::get('/admin/hapus-pegawai/{id}', [AdminController::class, 'destroy'])->name('admin.pegawai.destroy');

    Route::get('/admin/profil', [AdminController::class, 'profil'])->name('admin.profil');
    Route::post('/admin/profil/update', [AdminController::class, 'updateProfil'])->name('admin.profil.update');

    // FITUR: LOG AUDIT & EKSPOR CSV
    Route::get('/admin/audit', [AdminController::class, 'auditLog'])->name('admin.audit');
    Route::get('/admin/audit/export', [AdminController::class, 'exportAuditCsv'])->name('admin.audit.export');

    // FITUR: PERSETUJUAN RESET PASSWORD PEGAWAI
    Route::post('/admin/reset-password-request/{id}/approve', [AdminController::class, 'approveResetRequest'])->name('admin.reset.approve');
    Route::post('/admin/reset-password-request/{id}/reject', [AdminController::class, 'rejectResetRequest'])->name('admin.reset.reject');

    // FITUR: KONFIGURASI SISTEM GLOBAL (SALDO MENGENDAP, SCANNER & FORMAT REKENING)
    Route::get('/admin/konfigurasi', [AdminController::class, 'konfigurasi'])->name('admin.konfigurasi');
    Route::post('/admin/konfigurasi', [AdminController::class, 'updateKonfigurasi'])->name('admin.konfigurasi.update');
});

// =========================================================================
// RUTE AUDIT JURNAL AKUNTANSI & LAPORAN (ADMIN, SUPERVISOR, ACCOUNTING)
// =========================================================================
Route::middleware(['auth', 'role:admin,supervisor,accounting'])->group(function () {
    Route::get('/laporan/jurnal', [LaporanKeuanganController::class, 'jurnalAkuntansi'])->name('laporan.jurnal');
    Route::get('/laporan/saldo-produk', [LaporanKeuanganController::class, 'saldoPerProduk'])->name('laporan.saldo-produk');
    Route::get('/laporan/tahunan', [LaporanKeuanganController::class, 'laporanTahunan'])->name('laporan.tahunan');
});

// =========================================================================
// RUTE TELLER (TRANSAKSI, DAILY REPORT & KOREKSI)
// =========================================================================
Route::middleware(['auth', 'role:teller'])->group(function () {
    Route::get('/teller/dashboard', [TellerController::class, 'index'])->name('teller.dashboard');
    Route::get('/teller/transaksi/{id}', [TellerController::class, 'formTransaksi'])->name('teller.transaksi.form');
    Route::post('/teller/transaksi/proses', [TellerController::class, 'prosesTransaksi'])->name('teller.transaksi.proses');
    Route::get('/teller/riwayat', [TellerController::class, 'riwayat'])->name('teller.riwayat');
    Route::get('/teller/scan', [TellerController::class, 'scanQr'])->name('teller.scan');
    Route::get('/teller/cari-nis/{nis}', [TellerController::class, 'cariByNis'])->name('teller.cari-nis');
    Route::get('/teller/profil', [TellerController::class, 'profil'])->name('teller.profil');
    Route::post('/teller/profil/update', [TellerController::class, 'updateProfil'])->name('teller.profil.update');
    Route::post('/teller/transaksi/void/{id}', [TellerController::class, 'voidTransaksi'])->name('teller.transaksi.void');

    // DAILY REPORT TUTUP KAS TELLER
    Route::get('/teller/daily-report', [DailyReportController::class, 'index'])->name('teller.daily-report');
    Route::post('/teller/daily-report/simpan', [DailyReportController::class, 'store'])->name('teller.daily-report.store');

    // PENGAJUAN KAS TELLER (SAAT KAS HABIS / MENIPIS)
    Route::get('/teller/pengajuan-kas', [PengajuanKasController::class, 'indexTeller'])->name('teller.pengajuan-kas');
    Route::post('/teller/pengajuan-kas/kirim', [PengajuanKasController::class, 'store'])->name('teller.pengajuan-kas.store');
});

// =========================================================================
// RUTE SUPERVISOR & ACCOUNTING (APPROVAL & PENGELOLA LAPORAN)
// =========================================================================
Route::middleware(['auth', 'role:supervisor,accounting'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/supervisor/laporan', [SupervisorController::class, 'laporan'])->name('supervisor.laporan');
    Route::get('/supervisor/approve/{id}', [SupervisorController::class, 'approve'])->name('supervisor.approve');
    Route::get('/supervisor/cetak-harian', [SupervisorController::class, 'cetakHarian'])->name('supervisor.cetak-harian');
    Route::get('/supervisor/profil', [SupervisorController::class, 'profil'])->name('supervisor.profil');
    Route::post('/supervisor/profil/update', [SupervisorController::class, 'updateProfil'])->name('supervisor.profil.update');

    // DAILY REPORT APPROVAL / HOLD
    Route::get('/supervisor/daily-report', [DailyReportController::class, 'index'])->name('supervisor.daily-report');
    Route::post('/supervisor/daily-report/status/{id}', [DailyReportController::class, 'updateStatus'])->name('supervisor.daily-report.status');

    // PENGAJUAN KAS APPROVAL (DROPING DANA OPERASIONAL LOKET)
    Route::get('/supervisor/pengajuan-kas', [PengajuanKasController::class, 'indexSupervisor'])->name('supervisor.pengajuan-kas');
    Route::post('/supervisor/pengajuan-kas/setujui/{id}', [PengajuanKasController::class, 'setujui'])->name('supervisor.pengajuan-kas.setujui');
    Route::post('/supervisor/pengajuan-kas/tolak/{id}', [PengajuanKasController::class, 'tolak'])->name('supervisor.pengajuan-kas.tolak');
});

// PENGATURAN KAS AWAL HARI INI (DISET OLEH PETUGAS)
Route::middleware(['auth'])->group(function () {
    Route::post('/kas-awal/set', [PengajuanKasController::class, 'setKasAwal'])->name('kas-awal.set');
});

// =========================================================================
// RUTE MONITORING PETUGAS AVALIS & KUNJUNGAN SISWA (SEMUA ROLE PEGAWAI)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/absensi-avalis', [AbsensiAvalisController::class, 'index'])->name('admin.absensi-avalis');
    Route::get('/teller/absensi-avalis', [AbsensiAvalisController::class, 'index'])->name('teller.absensi-avalis');
    Route::get('/supervisor/absensi-avalis', [AbsensiAvalisController::class, 'index'])->name('supervisor.absensi-avalis');
    Route::get('/absensi-avalis', [AbsensiAvalisController::class, 'index'])->name('absensi.index');
    Route::get('/avalis/petugas', [AvalisPetugasController::class, 'index'])->name('avalis.petugas.index');
    Route::get('/kunjungan-siswa', [AbsensiAvalisController::class, 'kunjunganSiswa'])->name('kunjungan-siswa.index');
    Route::post('/kunjungan-siswa/verifikasi', [AbsensiAvalisController::class, 'simpanVerifikasiKunjungan'])->name('kunjungan-siswa.verifikasi');

    // RUTE BUKU TABUNGAN & SLIP TRANSAKSI RESMI
    Route::get('/buku-tabungan/{id}', [BukuTabunganController::class, 'show'])->name('buku-tabungan.show');
    Route::get('/buku-tabungan/cetak/{id}', [BukuTabunganController::class, 'cetakBuku'])->name('buku-tabungan.cetak');
    Route::get('/teller/slip-setor/{id}', [BukuTabunganController::class, 'cetakSlipSetor'])->name('teller.slip-setor');
    Route::get('/teller/slip-tarik/{id}', [BukuTabunganController::class, 'cetakSlipTarik'])->name('teller.slip-tarik');
    Route::post('/admin/nasabah/ubah-status/{id}', [BukuTabunganController::class, 'ubahStatus'])->name('admin.nasabah.ubah-status');

    // CETAK LEMBAR DAILY REPORT
    Route::get('/daily-report/cetak/{id}', [DailyReportController::class, 'cetak'])->name('daily-report.cetak');

    // PORTAL DASHBOARD NASABAH
    Route::get('/nasabah/dashboard', [NasabahController::class, 'dashboardNasabah'])->name('nasabah.dashboard');
});

// =========================================================================
// RUTE KHUSUS ADMINISTRATOR (PENGELOLAAN ABSENSI & MASTER AVALIS PENUH)
// =========================================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/absensi-avalis/simpan', [AbsensiAvalisController::class, 'store'])->name('absensi.store');
    Route::post('/absensi-avalis/update/{id}', [AbsensiAvalisController::class, 'update'])->name('absensi.update');
    Route::post('/absensi-avalis/ubah-shift/{id}', [AbsensiAvalisController::class, 'ubahShift'])->name('absensi.ubah-shift');
    Route::post('/absensi-avalis/ubah-status/{id}', [AbsensiAvalisController::class, 'ubahStatus'])->name('absensi.ubah-status');
    Route::get('/absensi-avalis/hapus/{id}', [AbsensiAvalisController::class, 'destroy'])->name('absensi.destroy');
    Route::get('/absensi-avalis/export', [AbsensiAvalisController::class, 'exportCsv'])->name('absensi.export');
    Route::match(['get', 'post'], '/absensi-avalis/sync-from-petugas', [AbsensiAvalisController::class, 'syncFromUsers'])->name('absensi.sync');

    Route::post('/avalis/petugas/simpan', [AvalisPetugasController::class, 'store'])->name('avalis.petugas.store');
    Route::post('/avalis/petugas/bulk', [AvalisPetugasController::class, 'storeBulk'])->name('avalis.petugas.bulk');
    Route::post('/avalis/petugas/sync/{id}', [AvalisPetugasController::class, 'syncKeAbsensi'])->name('avalis.petugas.sync-id');
    Route::match(['get', 'post'], '/avalis/petugas/sync-from-petugas', [AvalisPetugasController::class, 'syncFromUsers'])->name('avalis.petugas.sync');
    Route::get('/avalis/petugas/hapus/{id}', [AvalisPetugasController::class, 'destroy'])->name('avalis.petugas.destroy');

    Route::post('/kunjungan-siswa/reset/{id}', [AbsensiAvalisController::class, 'resetVerifikasiKunjungan'])->name('kunjungan-siswa.reset');
});

// HELPER DATABASE & STORAGE
Route::get('/fix-database-pegawai', fn () => 'OK')->name('fix.db.pegawai');
Route::get('/fix-database-void', fn () => 'OK')->name('fix.db.void');
Route::get('/fix-foto', function () {
    Artisan::call('storage:link');

    return 'OK';
})->name('fix.foto');
