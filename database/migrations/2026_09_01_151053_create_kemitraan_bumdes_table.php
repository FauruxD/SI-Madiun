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
        Schema::create('kemitraan_bumdes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bumdes_id')->constrained('bumdes')->cascadeOnDelete();
            $table->string('nama_mitra')->nullable();
            $table->string('jenis_kemitraan');
            $table->text('deskripsi')->nullable();
            $table->string('status', 20);
            $table->decimal('kebutuhan_investasi', 19, 2)->nullable();
            $table->timestamps();

            $table->index(['bumdes_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kemitraan_bumdes');
    }
};
