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
        // 1. Tambah kolom status dan kategori pada tabel nasabah
        Schema::table('nasabah', function (Blueprint $table) {
            if (! Schema::hasColumn('nasabah', 'status')) {
                $table->string('status', 20)->default('aktif')->after('saldo'); // aktif, alumni, nonaktif
            }
            if (! Schema::hasColumn('nasabah', 'kategori')) {
                $table->string('kategori', 30)->default('siswa')->after('status'); // siswa, guru_karyawan
            }
        });

        // 2. Tambah kolom jenis_layanan pada tabel transaksi untuk slip
        Schema::table('transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi', 'jenis_layanan')) {
                $table->string('jenis_layanan', 50)->default('Tabungan')->after('jenis_transaksi'); // Tabungan, Angsuran Pinjaman, Deposito Berjangka
            }
        });

        // 3. Pastikan nasabah yang ada berstatus aktif dan siswa
        DB::table('nasabah')->whereNull('status')->orWhere('status', '')->update(['status' => 'aktif']);
        DB::table('nasabah')->whereNull('kategori')->orWhere('kategori', '')->update(['kategori' => 'siswa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            if (Schema::hasColumn('nasabah', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('nasabah', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });

        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'jenis_layanan')) {
                $table->dropColumn('jenis_layanan');
            }
        });
    }
};
