<?php

use App\Enums\JenisBumdes;
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
        Schema::create('bumdes', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis', 20)->default(JenisBumdes::Bumdes->value);
            $table->string('nomor_registrasi', 100)->nullable()->unique();
            $table->unsignedSmallInteger('tahun_berdiri')->nullable();
            $table->string('status_hukum')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis', 'nama']);
            $table->index(['status_hukum', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bumdes');
    }
};
