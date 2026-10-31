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
        // 1. Tambah kolom pelacakan void & koreksi pada tabel transaksi
        Schema::table('transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi', 'ref_transaksi_id')) {
                $table->unsignedBigInteger('ref_transaksi_id')->nullable()->after('idempotency_key');
                $table->foreign('ref_transaksi_id')->references('id')->on('transaksi')->onDelete('set null');
            }
            if (! Schema::hasColumn('transaksi', 'dibatalkan_oleh')) {
                $table->unsignedBigInteger('dibatalkan_oleh')->nullable()->after('ref_transaksi_id');
                $table->foreign('dibatalkan_oleh')->references('id')->on('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('transaksi', 'dibatalkan_at')) {
                $table->timestamp('dibatalkan_at')->nullable()->after('dibatalkan_oleh');
            }
        });

        // 2. Tambah kolom total_cash_drop pada tabel daily_reports untuk pencatatan rekonsiliasi kas
        Schema::table('daily_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_reports', 'total_cash_drop')) {
                $table->bigInteger('total_cash_drop')->default(0)->after('total_deposit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'ref_transaksi_id')) {
                $table->dropForeign(['ref_transaksi_id']);
                $table->dropColumn('ref_transaksi_id');
            }
            if (Schema::hasColumn('transaksi', 'dibatalkan_oleh')) {
                $table->dropForeign(['dibatalkan_oleh']);
                $table->dropColumn('dibatalkan_oleh');
            }
            if (Schema::hasColumn('transaksi', 'dibatalkan_at')) {
                $table->dropColumn('dibatalkan_at');
            }
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'total_cash_drop')) {
                $table->dropColumn('total_cash_drop');
            }
        });
    }
};
