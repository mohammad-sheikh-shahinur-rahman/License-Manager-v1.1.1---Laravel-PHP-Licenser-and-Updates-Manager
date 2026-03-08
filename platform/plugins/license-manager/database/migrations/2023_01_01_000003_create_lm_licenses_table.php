<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_licenses')) {
            return;
        }

        Schema::create('lm_licenses', function (Blueprint $table): void {
            $table->id();
            $table->string('product_reference_id', 50)->index();
            $table->string('license_code')->unique();
            $table->string('type', 50)->nullable();
            $table->string('invoice', 100)->nullable()->index();
            $table->boolean('is_envato')->default(false);
            $table->string('customer_id')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->integer('uses')->default(0);
            $table->integer('parallel_uses')->nullable();
            $table->date('expires_at')->nullable();
            $table->integer('expiry_days')->nullable();
            $table->date('updates_until')->nullable();
            $table->date('support_until')->nullable();
            $table->text('domains')->nullable();
            $table->text('ips')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->timestamps();

            $table->index(['product_reference_id', 'is_valid']);
            $table->index(['customer_id', 'is_valid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_licenses');
    }
};
