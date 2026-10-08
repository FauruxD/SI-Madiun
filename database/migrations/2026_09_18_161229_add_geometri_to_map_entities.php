<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['kawasan_perdesaan', 'bumdes', 'potensi_desa'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->json('geometri')->nullable();
            });
        }

        Schema::create('titik_informasi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kawasan_id')->constrained('kawasan_perdesaan')->restrictOnDelete();
            $table->string('nama', 120);
            $table->string('jenis', 40);
            $table->string('deskripsi', 500)->nullable();
            $table->json('geometri');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['kawasan_id', 'jenis', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titik_informasi');

        foreach (['kawasan_perdesaan', 'bumdes', 'potensi_desa'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('geometri');
            });
        }
    }
};
