<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawasan_kategori', function (Blueprint $table): void {
            $table->foreignId('kawasan_id')->constrained('kawasan_perdesaan')->cascadeOnDelete();
            $table->foreignId('kategori_kawasan_id')->constrained('kategori_kawasan')->restrictOnDelete();
            $table->timestamps();

            $table->primary(['kawasan_id', 'kategori_kawasan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan_kategori');
    }
};
