<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lm_products', function (Blueprint $table): void {
            $table->string('default_license_type', 50)->nullable();
            $table->integer('default_uses')->nullable();
            $table->integer('default_parallel_uses')->nullable();
            $table->integer('default_expiry_days')->nullable();
            $table->integer('default_updates_until_days')->nullable();
            $table->integer('default_support_until_days')->nullable();
            $table->text('default_comments')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lm_products', function (Blueprint $table): void {
            $table->dropColumn([
                'default_license_type',
                'default_uses',
                'default_parallel_uses',
                'default_expiry_days',
                'default_updates_until_days',
                'default_support_until_days',
                'default_comments',
            ]);
        });
    }
};
