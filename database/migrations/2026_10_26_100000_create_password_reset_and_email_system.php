<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom email ke tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email', 150)->nullable()->after('no_hp');
            }
        });

        // 2. Buat tabel permohonan reset password & kode verifikasi OTP
        if (! Schema::hasTable('password_reset_requests')) {
            Schema::create('password_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('username', 50)->index();
                $table->string('nama_pemohon', 100)->nullable();
                $table->string('metode', 20)->default('whatsapp'); // 'whatsapp', 'email', 'ticket'
                $table->string('kontak', 100)->nullable(); // Nomor WA atau email
                $table->string('otp_code', 10)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->enum('status', ['pending', 'verified', 'completed', 'rejected'])->default('pending');
                $table->text('keterangan')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
