<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel 'jurnal' yang digunakan oleh TellerController & AdminController
        if (! Schema::hasTable('jurnal')) {
            Schema::create('jurnal', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transaksi_id');
                $table->text('keterangan')->nullable();
                $table->bigInteger('debit')->default(0);
                $table->bigInteger('kredit')->default(0);
                $table->date('tanggal');
                $table->timestamps();

                $table->foreign('transaksi_id')->references('id')->on('transaksi')->onDelete('cascade');
            });
        }

        // Tabel 'brankas' yang digunakan oleh TellerController
        if (! Schema::hasTable('brankas')) {
            Schema::create('brankas', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->unique();
                $table->bigInteger('saldo_awal')->default(0);
                $table->bigInteger('masuk')->default(0);
                $table->bigInteger('keluar')->default(0);
                $table->bigInteger('total')->default(0);
                $table->timestamps();
            });
        }

        // Tambah kolom yang kurang di tabel 'transaksi'
        Schema::table('transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('transaksi', 'petugas_id')) {
                $table->unsignedBigInteger('petugas_id')->nullable()->after('nasabah_id');
                $table->foreign('petugas_id')->references('id')->on('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('transaksi', 'alasan_batal')) {
                $table->text('alasan_batal')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal');
        Schema::dropIfExists('brankas');

        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'petugas_id')) {
                $table->dropForeign(['petugas_id']);
                $table->dropColumn('petugas_id');
            }
            if (Schema::hasColumn('transaksi', 'alasan_batal')) {
                $table->dropColumn('alasan_batal');
            }
        });
    }
};
