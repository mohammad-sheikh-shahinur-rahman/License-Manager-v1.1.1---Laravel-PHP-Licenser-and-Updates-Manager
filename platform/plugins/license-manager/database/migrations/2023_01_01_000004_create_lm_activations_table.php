<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_activations')) {
            return;
        }

        Schema::create('lm_activations', function (Blueprint $table): void {
            $table->id();
            $table->string('product_reference_id', 50)->index();
            $table->string('customer_id')->nullable()->index();
            $table->string('license_code')->index();
            $table->string('url')->nullable();
            $table->string('ip_address', 50)->nullable()->index();
            $table->timestamp('activated_at')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['license_code', 'is_active']);
            $table->index(['product_reference_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_activations');
    }
};
