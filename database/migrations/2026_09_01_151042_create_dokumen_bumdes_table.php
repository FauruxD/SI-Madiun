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
        Schema::create('dokumen_bumdes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bumdes_id')->constrained('bumdes')->cascadeOnDelete();
            $table->string('jenis_dokumen', 20);
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->string('nama_file');
            $table->string('file_path');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->text('ringkasan_maksud_tujuan')->nullable();
            $table->decimal('ringkasan_modal_awal', 19, 2)->nullable();
            $table->text('ringkasan_pembagian_shu')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bumdes_id', 'jenis_dokumen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_bumdes');
    }
};
