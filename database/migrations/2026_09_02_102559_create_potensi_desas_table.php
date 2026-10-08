<?php

use App\Enums\PotensiStatus;
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
        Schema::create('potensi_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->restrictOnDelete();
            $table->foreignId('kategori_potensi_id')->constrained('kategori_potensi')->restrictOnDelete();
            $table->foreignId('bumdes_id')->nullable()->constrained('bumdes')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('data_pendukung')->nullable();
            $table->decimal('kebutuhan_investasi', 19, 2)->nullable();
            $table->string('status', 30)->default(PotensiStatus::Draft->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['desa_id', 'status']);
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potensi_desa');
    }
};
