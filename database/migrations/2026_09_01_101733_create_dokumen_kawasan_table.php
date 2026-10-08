<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_kawasan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kawasan_id')->constrained('kawasan_perdesaan')->cascadeOnDelete();
            $table->string('jenis_dokumen', 30);
            $table->string('nomor_dokumen')->nullable();
            $table->string('nama_file');
            $table->string('file_path');
            $table->date('tanggal_dokumen')->nullable();
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kawasan_id', 'jenis_dokumen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_kawasan');
    }
};
