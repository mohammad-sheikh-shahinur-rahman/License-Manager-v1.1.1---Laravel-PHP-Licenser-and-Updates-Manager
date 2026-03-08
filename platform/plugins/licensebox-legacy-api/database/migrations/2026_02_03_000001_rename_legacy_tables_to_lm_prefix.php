<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    protected array $tableMapping = [
        'product_details' => 'lm_products',
        'product_versions' => 'lm_product_versions',
        'product_licenses' => 'lm_licenses',
        'product_activations' => 'lm_activations',
        'activity_logs' => 'lm_activity_logs',
        'update_downloads' => 'lm_update_downloads',
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
        foreach (array_flip($this->tableMapping) as $newTable => $oldTable) {
            if (Schema::hasTable($newTable) && ! Schema::hasTable($oldTable)) {
                Schema::rename($newTable, $oldTable);
            }
        }
    }
};
