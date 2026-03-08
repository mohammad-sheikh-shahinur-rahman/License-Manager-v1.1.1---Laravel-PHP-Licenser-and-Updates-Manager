<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_update_downloads')) {
            return;
        }

        Schema::create('lm_update_downloads', function (Blueprint $table): void {
            $table->id();
            $table->string('download_id', 50)->nullable()->unique();
            $table->string('product_reference_id', 50)->index();
            $table->string('version_id', 50)->nullable()->index();
            $table->string('url')->nullable();
            $table->string('ip_address', 50)->nullable()->index();
            $table->boolean('is_valid')->default(true);
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['product_reference_id', 'is_valid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_update_downloads');
    }
};
