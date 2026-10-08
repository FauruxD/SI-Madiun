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
        Schema::create('media_potensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('potensi_desa_id')->constrained('potensi_desa')->cascadeOnDelete();
            $table->string('tipe_media', 20);
            $table->string('judul')->nullable();
            $table->string('nama_file')->nullable();
            $table->string('file_path')->nullable();
            $table->string('media_url', 2048)->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['potensi_desa_id', 'tipe_media', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_potensi');
    }
};
