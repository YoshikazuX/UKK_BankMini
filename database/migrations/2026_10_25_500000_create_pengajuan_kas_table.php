<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengajuan_kas')) {
            Schema::create('pengajuan_kas', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_pengajuan', 50)->unique();
                $table->unsignedBigInteger('user_id'); // Teller pemohon
                $table->date('tanggal');
                $table->bigInteger('nominal');
                $table->text('alasan')->nullable();
                $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
                $table->unsignedBigInteger('disetujui_oleh')->nullable(); // SPV / Accounting
                $table->text('catatan_spv')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('disetujui_oleh')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kas');
    }
};
