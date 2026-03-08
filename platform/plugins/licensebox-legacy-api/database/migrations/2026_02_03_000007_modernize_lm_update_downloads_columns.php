<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_update_downloads')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_update_downloads', 'product_reference_id')) {
            return;
        }

        // Note: DDL statements cause implicit commits in MySQL

        $columnRenames = [
            'did' => 'download_id',
            'product' => 'product_reference_id',
            'vid' => 'version_id',
            'ip' => 'ip_address',
            'isvalid' => 'is_valid',
            'download_date' => 'downloaded_at',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_update_downloads', $old) && ! Schema::hasColumn('lm_update_downloads', $new)) {
                Schema::table('lm_update_downloads', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Add timestamps if missing
        if (! Schema::hasColumn('lm_update_downloads', 'created_at')) {
            Schema::table('lm_update_downloads', function (Blueprint $table): void {
                $table->timestamps();
            });

            // Backfill with downloaded_at
            DB::statement('UPDATE lm_update_downloads SET created_at = downloaded_at WHERE downloaded_at IS NOT NULL');
            DB::table('lm_update_downloads')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        // Add indexes (skip TEXT columns that can't be indexed without length)
        $this->addIndexIfNotExists('lm_update_downloads', 'download_id', 'unique');
        $this->addIndexIfNotExists('lm_update_downloads', 'product_reference_id');
        $this->addIndexIfNotExists('lm_update_downloads', 'version_id');
        // ip_address may be TEXT type, skip indexing it
        $this->addCompositeIndexIfNotExists('lm_update_downloads', ['product_reference_id', 'is_valid']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_update_downloads')) {
            return;
        }

        if (! Schema::hasColumn('lm_update_downloads', 'product_reference_id')) {
            return;
        }

        $columnRenames = [
            'download_id' => 'did',
            'product_reference_id' => 'product',
            'version_id' => 'vid',
            'ip_address' => 'ip',
            'is_valid' => 'isvalid',
            'downloaded_at' => 'download_date',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_update_downloads', $old)) {
                Schema::table('lm_update_downloads', function (Blueprint $table) use ($old, $new): void {
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
