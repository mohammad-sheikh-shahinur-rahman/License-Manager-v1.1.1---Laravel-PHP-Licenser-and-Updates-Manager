<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_products')) {
            return;
        }

        Schema::create('lm_products', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_id', 50)->unique();
            $table->string('envato_id', 50)->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('license_update')->default(false);
            $table->boolean('serve_latest_updates')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_products');
    }
};
