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
        Schema::create('review_eproposal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eproposal_id')->constrained('eproposal')->restrictOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status_sebelum', 30)->nullable();
            $table->string('status_sesudah', 30);
            $table->string('aksi', 50);
            $table->text('catatan')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->index(['eproposal_id', 'reviewed_at']);
            $table->index(['reviewer_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_eproposal');
    }
};
