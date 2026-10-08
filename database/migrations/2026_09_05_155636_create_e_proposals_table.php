<?php

use App\Enums\EProposalStatus;
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
        Schema::create('eproposal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('potensi_desa_id')->unique()->constrained('potensi_desa')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor_proposal')->nullable();
            $table->string('status', 30)->default(EProposalStatus::Diajukan->value);
            $table->text('catatan_pengaju')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
            $table->index(['status', 'published_at']);
            $table->index('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eproposal');
    }
};
