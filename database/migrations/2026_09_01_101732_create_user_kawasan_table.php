<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_kawasan', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kawasan_id')->constrained('kawasan_perdesaan')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'kawasan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_kawasan');
    }
};
