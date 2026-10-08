<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawasan_desa', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kawasan_id')->constrained('kawasan_perdesaan')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->restrictOnDelete();
            $table->date('tanggal_bergabung')->nullable();
            $table->timestamps();

            $table->unique(['kawasan_id', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan_desa');
    }
};
