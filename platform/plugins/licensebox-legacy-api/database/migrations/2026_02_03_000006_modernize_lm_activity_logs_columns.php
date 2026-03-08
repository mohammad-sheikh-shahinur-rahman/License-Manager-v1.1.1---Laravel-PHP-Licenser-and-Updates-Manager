<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lm_activity_logs')) {
            return;
        }

        // Skip if already modernized
        if (Schema::hasColumn('lm_activity_logs', 'message')) {
            return;
        }

        // Note: DDL statements cause implicit commits in MySQL

        // Handle primary key transformation (al_id -> id)
        if (Schema::hasColumn('lm_activity_logs', 'al_id') && ! Schema::hasColumn('lm_activity_logs', 'id')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->first();
            });

            DB::statement('UPDATE lm_activity_logs SET id = al_id');

            // Remove auto_increment from al_id first, then drop primary key
            DB::statement('ALTER TABLE lm_activity_logs MODIFY al_id INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE lm_activity_logs DROP PRIMARY KEY');
            DB::statement('ALTER TABLE lm_activity_logs ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE lm_activity_logs DROP COLUMN al_id');

            // Make id auto-increment
            DB::statement('ALTER TABLE lm_activity_logs MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        // Rename al_log to message
        if (Schema::hasColumn('lm_activity_logs', 'al_log') && ! Schema::hasColumn('lm_activity_logs', 'message')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->renameColumn('al_log', 'message');
            });
        }

        // Add type column if missing
        if (! Schema::hasColumn('lm_activity_logs', 'type')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->string('type', 50)->nullable()->after('id');
            });

            $this->addIndexIfNotExists('lm_activity_logs', 'type');
        }

        // Handle timestamps - rename al_date to created_at
        if (Schema::hasColumn('lm_activity_logs', 'al_date') && ! Schema::hasColumn('lm_activity_logs', 'created_at')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->renameColumn('al_date', 'created_at');
            });
        }

        // Add updated_at if missing
        if (! Schema::hasColumn('lm_activity_logs', 'updated_at')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->timestamp('updated_at')->nullable();
            });

            DB::table('lm_activity_logs')
                ->whereNull('updated_at')
                ->whereNotNull('created_at')
                ->update(['updated_at' => DB::raw('created_at')]);

            DB::table('lm_activity_logs')
                ->whereNull('updated_at')
                ->update(['updated_at' => Carbon::now()]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lm_activity_logs')) {
            return;
        }

        if (! Schema::hasColumn('lm_activity_logs', 'message')) {
            return;
        }

        if (Schema::hasColumn('lm_activity_logs', 'message')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->renameColumn('message', 'al_log');
            });
        }

        if (Schema::hasColumn('lm_activity_logs', 'created_at')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->renameColumn('created_at', 'al_date');
            });
        }

        if (Schema::hasColumn('lm_activity_logs', 'type')) {
            Schema::table('lm_activity_logs', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
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
};
