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
        Schema::table('nasabah', function (Blueprint $table) {
            if (! Schema::hasColumn('nasabah', 'email')) {
                $table->string('email', 150)->nullable()->after('username');
            }
            if (! Schema::hasColumn('nasabah', 'no_hp')) {
                $table->string('no_hp', 25)->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            if (Schema::hasColumn('nasabah', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('nasabah', 'no_hp')) {
                $table->dropColumn('no_hp');
            }
        });
    }
};
