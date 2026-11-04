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
        if (! Schema::hasTable('pin_reset_requests')) {
            Schema::create('pin_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nasabah_id')->index();
                $table->string('no_rekening', 50)->index();
                $table->string('nis', 50)->index();
                $table->string('nama_siswa', 150);
                $table->string('alasan', 255)->nullable();
                $table->string('status', 20)->default('pending'); // pending, approved, completed, rejected
                $table->unsignedBigInteger('approved_by')->nullable(); // Admin yang approve
                $table->string('reset_token', 64)->nullable()->unique();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('nasabah_id')->references('id')->on('nasabah')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_reset_requests');
    }
};
