<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    protected array $tableMapping = [
        'ls_products' => 'lm_products',
        'ls_product_versions' => 'lm_product_versions',
        'ls_product_licenses' => 'lm_product_licenses',
        'ls_product_license_activations' => 'lm_product_license_activations',
        'ls_product_version_downloads' => 'lm_product_version_downloads',
    ];

    public function up(): void
    {
        foreach ($this->tableMapping as $oldTable => $newTable) {
            if (Schema::hasTable($oldTable) && ! Schema::hasTable($newTable)) {
                Schema::rename($oldTable, $newTable);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tableMapping as $oldTable => $newTable) {
            if (Schema::hasTable($newTable) && ! Schema::hasTable($oldTable)) {
                Schema::rename($newTable, $oldTable);
            }
        }
    }
};
