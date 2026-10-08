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
        Schema::create('keuangan_bumdes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bumdes_id')->constrained('bumdes')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->decimal('modal_awal', 19, 2)->nullable();
            $table->decimal('omzet', 19, 2)->nullable();
            $table->decimal('shu', 19, 2)->nullable();
            $table->decimal('kontribusi_pades', 19, 2)->nullable();
            $table->timestamps();

            $table->unique(['bumdes_id', 'tahun']);
            $table->index(['tahun', 'bumdes_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_bumdes');
    }
};
