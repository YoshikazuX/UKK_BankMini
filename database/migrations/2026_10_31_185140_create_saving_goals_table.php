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
        if (! Schema::hasTable('saving_goals')) {
            Schema::create('saving_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nasabah_id')->index();
                $table->string('nama', 150);
                $table->decimal('target_amount', 15, 2);
                $table->decimal('current_amount', 15, 2)->default(0.00);
                $table->string('icon', 50)->nullable()->default('savings');
                $table->date('deadline')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();

                $table->foreign('nasabah_id')->references('id')->on('nasabah')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_goals');
    }
};
