<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('verifikasi_kunjungan_siswa')) {
            Schema::create('verifikasi_kunjungan_siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nasabah_id')->index();
                $table->date('tanggal')->index();
                $table->string('status_kunjungan', 50)->default('BELUM DIVERIFIKASI');
                $table->dateTime('waktu_verifikasi')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index(); // ID User yang memverifikasi
                $table->string('nama_verifikator', 150)->nullable();
                $table->string('role_verifikator', 50)->nullable();
                $table->string('loket', 50)->nullable()->default('Loket 1');
                $table->string('shift', 100)->nullable();
                $table->unsignedBigInteger('petugas_avalis_id')->nullable();
                $table->string('petugas_avalis_nama', 150)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                // Cegah verifikasi ganda untuk siswa dan tanggal yang sama
                $table->unique(['nasabah_id', 'tanggal'], 'unique_siswa_tanggal_kunjungan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_kunjungan_siswa');
    }
};
