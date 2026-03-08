<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $renames = [
            'ls_customers' => 'lm_customers',
            'ls_customer_password_reset_tokens' => 'lm_customer_password_reset_tokens',
            'ls_api_keys' => 'lm_api_keys',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    public function down(): void
    {
        $renames = [
            'lm_customers' => 'ls_customers',
            'lm_customer_password_reset_tokens' => 'ls_customer_password_reset_tokens',
            'lm_api_keys' => 'lm_api_keys',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }
};
