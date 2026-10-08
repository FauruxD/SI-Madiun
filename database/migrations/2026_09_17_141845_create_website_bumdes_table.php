<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_bumdes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bumdes_id')->unique()->constrained('bumdes')->restrictOnDelete();
            $table->string('subdomain', 63)->unique();
            $table->string('custom_domain', 253)->nullable()->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        DB::table('bumdes')->orderBy('id')->chunkById(100, function ($profiles): void {
            foreach ($profiles as $profile) {
                DB::table('website_bumdes')->insert([
                    'bumdes_id' => $profile->id,
                    'subdomain' => Str::limit(Str::slug($profile->nama) ?: 'bumdes', min(50, 62 - strlen((string) $profile->id)), '').'-'.$profile->id,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_bumdes');
    }
};
