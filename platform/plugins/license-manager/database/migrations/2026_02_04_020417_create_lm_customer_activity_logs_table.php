<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_customer_activity_logs')) {
            return;
        }

        Schema::create('lm_customer_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_id', 50)->index();
            $table->string('type', 50)->index();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_customer_activity_logs');
    }
};
