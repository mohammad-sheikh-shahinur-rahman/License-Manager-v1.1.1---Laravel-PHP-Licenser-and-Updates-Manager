<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_activations')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_activations', 'product_reference_id')) {
            return;
        }

        // Note: DDL statements cause implicit commits in MySQL

        // Handle primary key transformation first (pi_id -> id)
        if (Schema::hasColumn('lm_activations', 'pi_id') && ! Schema::hasColumn('lm_activations', 'id')) {
            Schema::table('lm_activations', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->first();
            });

            DB::statement('UPDATE lm_activations SET id = pi_id');

            // Remove auto_increment from pi_id first, then drop primary key
            DB::statement('ALTER TABLE lm_activations MODIFY pi_id INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE lm_activations DROP PRIMARY KEY');
            DB::statement('ALTER TABLE lm_activations ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE lm_activations DROP COLUMN pi_id');

            // Make id auto-increment
            DB::statement('ALTER TABLE lm_activations MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $columnRenames = [
            'pi_product' => 'product_reference_id',
            'pi_client' => 'customer_id',
            'pi_license_code' => 'license_code',
            'pi_url' => 'url',
            'pi_ip' => 'ip_address',
            'pi_date' => 'activated_at',
            'pi_agent' => 'user_agent',
            'pi_isvalid' => 'is_valid',
            'pi_isactive' => 'is_active',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_activations', $old) && ! Schema::hasColumn('lm_activations', $new)) {
                Schema::table('lm_activations', function (Blueprint $table) use ($old, $new): void {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Drop pi_iid if exists (not in target schema)
        if (Schema::hasColumn('lm_activations', 'pi_iid')) {
            Schema::table('lm_activations', function (Blueprint $table): void {
                $table->dropColumn('pi_iid');
            });
        }

        // Add timestamps if missing
        if (! Schema::hasColumn('lm_activations', 'created_at')) {
            Schema::table('lm_activations', function (Blueprint $table): void {
                $table->timestamps();
            });

            // Backfill with activated_at
            DB::statement('UPDATE lm_activations SET created_at = activated_at WHERE activated_at IS NOT NULL');
            DB::table('lm_activations')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        // Add indexes (skip TEXT columns that can't be indexed without length)
        $this->addIndexIfNotExists('lm_activations', 'product_reference_id');
        // customer_id may be TEXT type, skip indexing it
        $this->addIndexIfNotExists('lm_activations', 'license_code');
        // ip_address may be TEXT type, skip indexing it
        $this->addCompositeIndexIfNotExists('lm_activations', ['license_code', 'is_active']);
        $this->addCompositeIndexIfNotExists('lm_activations', ['product_reference_id', 'is_active']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_activations')) {
            return;
        }

        if (! Schema::hasColumn('lm_activations', 'product_reference_id')) {
            return;
        }

        $columnRenames = [
            'product_reference_id' => 'pi_product',
            'customer_id' => 'pi_client',
            'license_code' => 'pi_license_code',
            'url' => 'pi_url',
            'ip_address' => 'pi_ip',
            'activated_at' => 'pi_date',
            'user_agent' => 'pi_agent',
            'is_valid' => 'pi_isvalid',
            'is_active' => 'pi_isactive',
        ];

        foreach ($columnRenames as $old => $new) {
            if (Schema::hasColumn('lm_activations', $old)) {
                Schema::table('lm_activations', function (Blueprint $table) use ($old, $new): void {
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
