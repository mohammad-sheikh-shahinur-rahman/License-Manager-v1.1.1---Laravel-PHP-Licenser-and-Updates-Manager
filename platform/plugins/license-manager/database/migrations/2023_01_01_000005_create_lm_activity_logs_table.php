<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_activity_logs')) {
            return;
        }

        Schema::create('lm_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50)->nullable()->index();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_activity_logs');
    }
};
