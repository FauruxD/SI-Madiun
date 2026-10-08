<?php

use App\Enums\AchievementStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_aksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_aksi_id')->constrained('rencana_aksi')->restrictOnDelete();
            $table->unsignedTinyInteger('periode_ke');
            $table->decimal('realisasi_nilai', 19, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal_realisasi')->nullable();
            $table->decimal('persentase_capaian', 30, 2)->nullable();
            $table->string('status', 20)->default(AchievementStatus::InProgress->value);
            $table->timestamps();

            $table->unique(['rencana_aksi_id', 'periode_ke']);
            $table->index(['status', 'tanggal_realisasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_aksi');
    }
};
