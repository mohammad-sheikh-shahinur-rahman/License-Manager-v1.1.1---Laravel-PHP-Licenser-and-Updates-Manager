<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_licenses')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_licenses', 'product_reference_id')) {
            return;
        }

        // Note: DDL statements cause implicit commits in MySQL

        $columnRenames = [
            'pid' => 'product_reference_id',
            'license_type' => 'type',
            'client' => 'customer_id',
            'expiry' => 'expires_at',
            'updates_till' => 'updates_until',
            'supported_till' => 'support_until',
            'validity' => 'is_valid',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_licenses', $old) && ! Schema::hasColumn('lm_licenses', $new)) {
                Schema::table('lm_licenses', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Handle timestamps - rename added_on to created_at
        if (Schema::hasColumn('lm_licenses', 'added_on') && ! Schema::hasColumn('lm_licenses', 'created_at')) {
            Schema::table('lm_licenses', function (Blueprint $table): void {
                $table->renameColumn('added_on', 'created_at');
            });
        }

        // Add updated_at if missing
        if (! Schema::hasColumn('lm_licenses', 'updated_at')) {
            Schema::table('lm_licenses', function (Blueprint $table): void {
                $table->timestamp('updated_at')->nullable();
            });

            DB::table('lm_licenses')
                ->whereNull('updated_at')
                ->whereNotNull('created_at')
                ->update(['updated_at' => DB::raw('created_at')]);

            DB::table('lm_licenses')
                ->whereNull('updated_at')
                ->update(['updated_at' => Carbon::now()]);
        }

        // Drop duplicate support_till column if exists (after renaming supported_till)
        if (Schema::hasColumn('lm_licenses', 'support_till')) {
            Schema::table('lm_licenses', function (Blueprint $table): void {
                $table->dropColumn('support_till');
            });
        }

        // Add indexes (skip TEXT columns that can't be indexed without length)
        $this->addIndexIfNotExists('lm_licenses', 'product_reference_id');
        // customer_id may be TEXT type, skip indexing it
        $this->addIndexIfNotExists('lm_licenses', 'email');
        $this->addCompositeIndexIfNotExists('lm_licenses', ['product_reference_id', 'is_valid']);
        // Skip composite index with customer_id since it may be TEXT type
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_licenses')) {
            return;
        }

        if (! Schema::hasColumn('lm_licenses', 'product_reference_id')) {
            return;
        }

        $columnRenames = [
            'product_reference_id' => 'pid',
            'type' => 'license_type',
            'customer_id' => 'client',
            'expires_at' => 'expiry',
            'updates_until' => 'updates_till',
            'support_until' => 'supported_till',
            'is_valid' => 'validity',
            'created_at' => 'added_on',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_licenses', $old)) {
                Schema::table('lm_licenses', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }
    }

    protected function addIndexIfNotExists(string $table, string $column): void
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

        Schema::table($table, function (Blueprint $table) use ($column): void {
            $table->index($column);
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
