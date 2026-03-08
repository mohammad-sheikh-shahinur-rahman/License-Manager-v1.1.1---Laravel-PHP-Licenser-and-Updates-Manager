<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    // Only drop old legacy table names that were renamed
    // Do NOT drop lm_products or lm_product_versions - they're used by license-manager
    protected array $unusedTables = [
        'lm_product_licenses',
        'lm_product_license_activations',
        'lm_product_version_downloads',
    ];

    public function up(): void
    {
        foreach ($this->unusedTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Tables were empty and unused - no need to recreate
    }
};
