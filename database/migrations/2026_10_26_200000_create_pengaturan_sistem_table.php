<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengaturan_sistem')) {
            Schema::create('pengaturan_sistem', function (Blueprint $table) {
                $table->id();
                $table->integer('saldo_mengendap_min')->default(10000);
                $table->boolean('scanner_kamera_aktif')->default(true);
                $table->string('tahun_ajaran_aktif', 20)->default('2026/2027');
                $table->string('format_no_rekening', 50)->default('TA-JURUSAN-URUT');
                $table->string('nama_bank', 100)->default('LKM Mitra Siswa Abadi');
                $table->integer('biaya_administrasi_buku')->default(5000);
                $table->timestamps();
            });

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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};
