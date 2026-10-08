<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_aksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bumdes_id')->constrained('bumdes')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('program');
            $table->string('kegiatan');
            $table->text('target_deskripsi')->nullable();
            $table->decimal('target_nilai', 19, 2)->nullable();
            $table->string('target_satuan', 100)->nullable();
            $table->string('periode', 20);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->index(['bumdes_id', 'tahun']);
            $table->index(['tahun', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_aksi');
    }
};
