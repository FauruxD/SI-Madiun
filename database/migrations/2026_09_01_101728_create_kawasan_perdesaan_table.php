<?php

use App\Enums\KawasanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawasan_perdesaan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_penetapan')->nullable();
            $table->string('status', 30)->default(KawasanStatus::Draft->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan_perdesaan');
    }
};
