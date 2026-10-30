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
        // 1. Buat Tabel daily_reports
        if (! Schema::hasTable('daily_reports')) {
            Schema::create('daily_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // Petugas Teller
                $table->date('report_date');
                $table->bigInteger('opening_balance')->default(0);
                $table->bigInteger('total_deposit')->default(0);
                $table->bigInteger('total_withdrawal')->default(0);
                $table->bigInteger('system_balance')->default(0);
                $table->bigInteger('physical_balance')->default(0);
                $table->bigInteger('difference')->default(0); // Selisih Kas Fisik vs Sistem
                $table->string('status', 20)->default('pending'); // pending, approved, hold, rejected
                $table->unsignedBigInteger('approved_by')->nullable(); // Supervisor / Accounting
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 2. Buat Tabel avalis (Data Petugas Teller & Asisten Jaga Siswa)
        if (! Schema::hasTable('avalis')) {
            Schema::create('avalis', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 150);
                $table->string('kelas', 50);
                $table->string('no_wa', 25);
                $table->unsignedBigInteger('user_id')->nullable(); // Hubungkan dengan akun teller jika ada
                $table->string('status', 20)->default('aktif');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 3. Tambah kolom pada tabel transaksi untuk Idempotency & Daily Report & Kode Sandi
        Schema::table('transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi', 'daily_report_id')) {
                $table->unsignedBigInteger('daily_report_id')->nullable()->after('petugas_id');
            }
            if (! Schema::hasColumn('transaksi', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->unique()->after('status');
            }
            if (! Schema::hasColumn('transaksi', 'kode_sandi')) {
                $table->string('kode_sandi', 10)->nullable()->after('jenis_transaksi'); // 101, 201, 102, 202, 301
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'daily_report_id')) {
                $table->dropColumn('daily_report_id');
            }
            if (Schema::hasColumn('transaksi', 'idempotency_key')) {
                $table->dropColumn('idempotency_key');
            }
            if (Schema::hasColumn('transaksi', 'kode_sandi')) {
                $table->dropColumn('kode_sandi');
            }
        });

        Schema::dropIfExists('avalis');
        Schema::dropIfExists('daily_reports');
    }
};
