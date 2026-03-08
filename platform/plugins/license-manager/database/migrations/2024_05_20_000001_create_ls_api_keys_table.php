<?php

use Botble\LicenseManager\Enums\ApiKeyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ls_api_keys')) {
            return;
        }

        Schema::create('ls_api_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 20)->default(ApiKeyType::External);
            $table->string('key')->unique();
            $table->text('scopes')->nullable();
            $table->boolean('special')->default(false);
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();

            $table->index(['type', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ls_api_keys');
    }
};
