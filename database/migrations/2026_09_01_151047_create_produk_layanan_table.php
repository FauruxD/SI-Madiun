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
        Schema::create('produk_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_usaha_id')->constrained('unit_usaha')->cascadeOnDelete();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->text('deskripsi');
            $table->decimal('harga', 19, 2)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['unit_usaha_id', 'is_active', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_layanan');
    }
};
