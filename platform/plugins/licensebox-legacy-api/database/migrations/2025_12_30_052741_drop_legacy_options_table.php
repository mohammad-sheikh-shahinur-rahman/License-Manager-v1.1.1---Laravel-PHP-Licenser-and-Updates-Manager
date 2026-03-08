<?php

use Botble\Setting\Facades\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('options')) {
            return;
        }

        $legacyOptions = DB::table('options')
            ->where('name', 'LIKE', 'ls_%')
            ->get();

        foreach ($legacyOptions as $option) {
            $value = $option->payload;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            if (setting($option->name) === null) {
                Setting::set($option->name, $value);
            }
        }

        Setting::save();

        Schema::dropIfExists('options');
    }

    public function down(): void
    {
    }
};
