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
        Schema::create('pengurus_bumdes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bumdes_id')->constrained('bumdes')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('foto_path')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['bumdes_id', 'is_active', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengurus_bumdes');
    }
};
