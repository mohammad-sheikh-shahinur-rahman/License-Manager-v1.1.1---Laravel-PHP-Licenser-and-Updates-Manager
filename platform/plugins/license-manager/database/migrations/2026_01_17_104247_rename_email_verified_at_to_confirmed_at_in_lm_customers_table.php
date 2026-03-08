<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_customers')) {
            return;
        }

        if (Schema::hasColumn('lm_customers', 'email_verified_at') && ! Schema::hasColumn('lm_customers', 'confirmed_at')) {
            Schema::table('lm_customers', function (Blueprint $table): void {
                $table->renameColumn('email_verified_at', 'confirmed_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_customers')) {
            return;
        }

        if (Schema::hasColumn('lm_customers', 'confirmed_at') && ! Schema::hasColumn('lm_customers', 'email_verified_at')) {
            Schema::table('lm_customers', function (Blueprint $table): void {
                $table->renameColumn('confirmed_at', 'email_verified_at');
            });
        }
    }
};
