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
        if (! Schema::hasTable('approval_histories')) {
            Schema::create('approval_histories', function (Blueprint $table) {
                $table->id();
                $table->string('pekerjaan_type', 50)->index(); // pengajuan_kas, daily_report, transaksi
                $table->unsignedBigInteger('pekerjaan_id')->index();
                $table->string('nomor_referensi', 100)->index();
                $table->unsignedBigInteger('teller_id')->nullable()->index();
                $table->string('nama_teller', 150);
                $table->unsignedBigInteger('supervisor_id')->nullable()->index();
                $table->string('nama_supervisor', 150);
                $table->string('jenis_keputusan', 50)->index(); // disetujui, ditolak, klarifikasi_hold
                $table->string('status_sebelum', 50);
                $table->string('status_sesudah', 50);
                $table->bigInteger('nominal')->nullable();
                $table->text('catatan')->nullable();
                $table->text('alasan_penolakan')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->foreign('teller_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        Schema::table('daily_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_reports', 'catatan_spv')) {
                $table->text('catatan_spv')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('daily_reports', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('catatan_spv');
            }
        });

        Schema::table('pengajuan_kas', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_kas', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('catatan_spv');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');

        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'catatan_spv')) {
                $table->dropColumn('catatan_spv');
            }
            if (Schema::hasColumn('daily_reports', 'alasan_penolakan')) {
                $table->dropColumn('alasan_penolakan');
            }
        });

        Schema::table('pengajuan_kas', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_kas', 'alasan_penolakan')) {
                $table->dropColumn('alasan_penolakan');
            }
        });
    }
};
