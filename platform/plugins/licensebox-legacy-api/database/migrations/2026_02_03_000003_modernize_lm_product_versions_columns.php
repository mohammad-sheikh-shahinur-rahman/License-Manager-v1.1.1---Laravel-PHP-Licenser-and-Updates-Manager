<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_product_versions')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_product_versions', 'version_id')) {
            return;
        }

        // Note: DDL statements cause implicit commits in MySQL

        $columnRenames = [
            'vid' => 'version_id',
            'pid' => 'product_reference_id',
            'release_date' => 'released_at',
            'status' => 'is_active',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_product_versions', $old) && ! Schema::hasColumn('lm_product_versions', $new)) {
                Schema::table('lm_product_versions', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Add timestamps if missing
        if (! Schema::hasColumn('lm_product_versions', 'created_at')) {
            Schema::table('lm_product_versions', function (Blueprint $table): void {
                $table->timestamps();
            });

            // Backfill with released_at if available
            DB::statement('UPDATE lm_product_versions SET created_at = released_at WHERE released_at IS NOT NULL');
            DB::table('lm_product_versions')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        // Add indexes
        $this->addIndexIfNotExists('lm_product_versions', 'version_id', 'unique');
        $this->addIndexIfNotExists('lm_product_versions', 'product_reference_id');
        $this->addCompositeIndexIfNotExists('lm_product_versions', ['product_reference_id', 'is_active']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_product_versions')) {
            return;
        }

        if (! Schema::hasColumn('lm_product_versions', 'version_id')) {
            return;
        }

        $columnRenames = [
            'version_id' => 'vid',
            'product_reference_id' => 'pid',
            'released_at' => 'release_date',
            'is_active' => 'status',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_product_versions', $old)) {
                Schema::table('lm_product_versions', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }
    }

    protected function addIndexIfNotExists(string $table, string $column, string $type = 'index'): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (in_array($column, $index['columns'], true) && count($index['columns']) === 1) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $table) use ($column, $type): void {
            if ($type === 'unique') {
                $table->unique($column);
            } else {
                $table->index($column);
            }
        });
    }

    protected function addCompositeIndexIfNotExists(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if ($index['columns'] === $columns) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $table) use ($columns): void {
            $table->index($columns);
        });
    }
};
