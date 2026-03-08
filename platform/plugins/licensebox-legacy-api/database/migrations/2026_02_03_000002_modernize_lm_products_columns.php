<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_products')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_products', 'reference_id')) {
            return;
        }

        // Note: DDL statements (ALTER TABLE) cause implicit commits in MySQL,
        // so we can't use DB::transaction() here

        // Rename columns
        $columnRenames = [
            'pd_pid' => 'reference_id',
            'pd_name' => 'name',
            'pd_details' => 'description',
            'pd_status' => 'is_active',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_products', $old) && ! Schema::hasColumn('lm_products', $new)) {
                Schema::table('lm_products', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Handle primary key transformation (pd_id -> id)
        if (Schema::hasColumn('lm_products', 'pd_id') && ! Schema::hasColumn('lm_products', 'id')) {
            // MySQL approach: cannot easily rename PK, so add new, copy, drop old
            Schema::table('lm_products', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->first();
            });

            DB::statement('UPDATE lm_products SET id = pd_id');

            // Remove auto_increment from pd_id first, then drop primary key
            DB::statement('ALTER TABLE lm_products MODIFY pd_id INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE lm_products DROP PRIMARY KEY');
            DB::statement('ALTER TABLE lm_products ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE lm_products DROP COLUMN pd_id');

            // Make id auto-increment
            DB::statement('ALTER TABLE lm_products MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        // Add timestamps if missing
        if (! Schema::hasColumn('lm_products', 'created_at')) {
            Schema::table('lm_products', function (Blueprint $table): void {
                $table->timestamps();
            });

            DB::table('lm_products')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        // Add indexes
        $this->addIndexIfNotExists('lm_products', 'reference_id', 'unique');
        $this->addIndexIfNotExists('lm_products', 'envato_id');
        $this->addIndexIfNotExists('lm_products', 'is_active');
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_products')) {
            return;
        }

        if (! Schema::hasColumn('lm_products', 'reference_id')) {
            return;
        }

        $columnRenames = [
            'reference_id' => 'pd_pid',
            'name' => 'pd_name',
            'description' => 'pd_details',
            'is_active' => 'pd_status',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_products', $old)) {
                Schema::table('lm_products', function (Blueprint $table) use ($old, $new): void {
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
        $indexName = $table . '_' . $column . '_' . ($type === 'unique' ? 'unique' : 'index');

        foreach ($indexes as $index) {
            if ($index['name'] === $indexName || in_array($column, $index['columns'], true)) {
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
};
