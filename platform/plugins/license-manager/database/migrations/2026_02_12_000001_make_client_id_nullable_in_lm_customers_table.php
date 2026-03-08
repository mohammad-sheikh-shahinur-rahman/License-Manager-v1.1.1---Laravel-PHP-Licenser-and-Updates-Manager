<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lm_customers', function (Blueprint $table): void {
            $table->string('client_id')->unique()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lm_customers', function (Blueprint $table): void {
            $table->string('client_id')->unique()->nullable(false)->change();
        });
    }
};
