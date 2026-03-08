<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ls_customers')) {
            Schema::create('ls_customers', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->foreignId('avatar_id')->nullable();
                $table->string('client_id')->unique();
                $table->timestamp('confirmed_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ls_customer_password_reset_tokens')) {
            Schema::create('ls_customer_password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ls_customer_password_reset_tokens');
        Schema::dropIfExists('ls_customers');
    }
};
