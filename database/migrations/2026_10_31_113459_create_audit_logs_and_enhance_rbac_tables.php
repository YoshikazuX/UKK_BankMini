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
        // 1. Ubah kolom role pada tabel users agar mendukung seluruh role PRD secara fleksibel
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('teller')->change();
        });

        // 2. Buat tabel audit_logs untuk jejak perubahan data
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('nama_user', 150);
                $table->string('role_user', 50)->nullable()->index();
                $table->string('jenis_aktivitas', 100)->index();
                $table->string('modul', 50)->index();
                $table->string('objek_id', 100)->nullable()->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tambahkan kolom handled_by dan handled_at pada password_reset_requests
        Schema::table('password_reset_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('password_reset_requests', 'handled_by')) {
                $table->unsignedBigInteger('handled_by')->nullable()->after('status')->index();
            }
            if (! Schema::hasColumn('password_reset_requests', 'handled_at')) {
                $table->timestamp('handled_at')->nullable()->after('handled_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_requests', 'handled_by')) {
                $table->dropColumn('handled_by');
            }
            if (Schema::hasColumn('password_reset_requests', 'handled_at')) {
                $table->dropColumn('handled_at');
            }
        });

        Schema::dropIfExists('audit_logs');
    }
};
