<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lm_product_versions')) {
            return;
        }

        Schema::create('lm_product_versions', function (Blueprint $table): void {
            $table->id();
            $table->string('version_id', 50)->unique();
            $table->string('product_reference_id', 50)->index();
            $table->string('version', 50);
            $table->date('released_at')->nullable();
            $table->text('summary')->nullable();
            $table->text('changelog')->nullable();
            $table->string('main_file')->nullable();
            $table->string('sql_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_reference_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lm_product_versions');
    }
};
