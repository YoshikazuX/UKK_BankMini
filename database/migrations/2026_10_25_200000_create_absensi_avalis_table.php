<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('absensi_avalis')) {
            Schema::create('absensi_avalis', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('nama');
                $table->string('kelas');
                $table->string('no_wa');
                $table->string('shift_jaga');
                $table->string('status')->default('Hadir');
                $table->string('keterangan')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();
            });

            // Data Awal Avalis agar saat pertama dibuka sudah terisi rapi seperti file Excel
            DB::table('absensi_avalis')->insert([
                [
                    'tanggal' => now()->toDateString(),
                    'nama' => 'Siti Nurhaliza',
                    'kelas' => 'XII AKL 1',
                    'no_wa' => '081234567890',
                    'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
                    'status' => 'Hadir',
                    'keterangan' => 'Standby Loket Pembantu & Verifikasi',
                    'created_by' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tanggal' => now()->toDateString(),
                    'nama' => 'Ahmad Fauzi Rahman',
                    'kelas' => 'XI BDP 2',
                    'no_wa' => '082198765432',
                    'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
                    'status' => 'Hadir',
                    'keterangan' => 'Pemandu Antrean Siswa',
                    'created_by' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tanggal' => now()->toDateString(),
                    'nama' => 'Rina Anggraeni',
                    'kelas' => 'XII AKL 2',
                    'no_wa' => '085712345678',
                    'shift_jaga' => 'Shift Siang (11.30 - 15.30)',
                    'status' => 'Hadir',
                    'keterangan' => 'Pencatatan Buku Kas Harian',
                    'created_by' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tanggal' => now()->toDateString(),
                    'nama' => 'Budi Pratama',
                    'kelas' => 'XI RPL 1',
                    'no_wa' => '087811223344',
                    'shift_jaga' => 'Shift Siang (11.30 - 15.30)',
                    'status' => 'Izin',
                    'keterangan' => 'Dispensasi Ujian Praktik',
                    'created_by' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tanggal' => now()->toDateString(),
                    'nama' => 'Dewi Sartika Putri',
                    'kelas' => 'XII AKL 3',
                    'no_wa' => '089655443322',
                    'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
                    'status' => 'Hadir',
                    'keterangan' => 'Rekap Setoran Tabungan Siswa',
                    'created_by' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_avalis');
    }
};
