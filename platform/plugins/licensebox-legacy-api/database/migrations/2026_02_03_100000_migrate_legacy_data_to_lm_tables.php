<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Migrate product_details -> lm_products
        $this->migrateProducts();

        // Migrate product_versions -> lm_product_versions
        $this->migrateProductVersions();

        // Migrate product_licenses -> lm_licenses
        $this->migrateLicenses();

        // Migrate product_activations -> lm_activations
        $this->migrateActivations();

        // Migrate activity_logs -> lm_activity_logs
        $this->migrateActivityLogs();

        // Migrate update_downloads -> lm_update_downloads
        $this->migrateUpdateDownloads();
    }

    protected function migrateProducts(): void
    {
        if (! Schema::hasTable('product_details') || ! Schema::hasTable('lm_products')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('product_details', 'pd_id')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_products')->count() > 0) {
            return;
        }

        DB::table('product_details')->orderBy('pd_id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->pd_id,
                    'reference_id' => $row->pd_pid,
                    'envato_id' => $row->envato_id ?? null,
                    'name' => $row->pd_name,
                    'description' => $row->pd_details ?? null,
                    'license_update' => $row->license_update ?? 0,
                    'serve_latest_updates' => $row->serve_latest_updates ?? 1,
                    'is_active' => $row->pd_status ?? 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_products')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_products')->count() . ' products');
    }

    protected function migrateProductVersions(): void
    {
        if (! Schema::hasTable('product_versions') || ! Schema::hasTable('lm_product_versions')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('product_versions', 'vid')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_product_versions')->count() > 0) {
            return;
        }

        DB::table('product_versions')->orderBy('id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->id,
                    'version_id' => $row->vid,
                    'product_reference_id' => $row->pid,
                    'version' => $row->version,
                    'released_at' => $row->release_date ?? null,
                    'summary' => $row->summary ?? null,
                    'changelog' => $row->changelog ?? null,
                    'main_file' => $row->main_file ?? null,
                    'sql_file' => $row->sql_file ?? null,
                    'is_active' => $row->status ?? 1,
                    'created_at' => $row->release_date ?? Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_product_versions')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_product_versions')->count() . ' product versions');
    }

    protected function migrateLicenses(): void
    {
        if (! Schema::hasTable('product_licenses') || ! Schema::hasTable('lm_licenses')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('product_licenses', 'pid')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_licenses')->count() > 0) {
            return;
        }

        DB::table('product_licenses')->orderBy('id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->id,
                    'product_reference_id' => $row->pid,
                    'license_code' => $row->license_code,
                    'type' => $row->license_type ?? null,
                    'invoice' => $row->invoice ?? null,
                    'is_envato' => $row->is_envato ?? 0,
                    'customer_id' => $row->client ?? null,
                    'email' => $row->email ?? null,
                    'uses' => $row->uses ?? 0,
                    'parallel_uses' => $row->parallel_uses ?? null,
                    'expires_at' => $row->expiry ?? null,
                    'expiry_days' => $row->expiry_days ?? null,
                    'updates_until' => $row->updates_till ?? null,
                    'support_until' => $row->supported_till ?? null,
                    'domains' => $row->domains ?? null,
                    'ips' => $row->ips ?? null,
                    'comments' => $row->comments ?? null,
                    'is_valid' => $row->validity ?? 1,
                    'created_at' => $row->added_on ?? Carbon::now(),
                    'updated_at' => $row->added_on ?? Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_licenses')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_licenses')->count() . ' licenses');
    }

    protected function migrateActivations(): void
    {
        if (! Schema::hasTable('product_activations') || ! Schema::hasTable('lm_activations')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('product_activations', 'pi_id')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_activations')->count() > 0) {
            return;
        }

        DB::table('product_activations')->orderBy('pi_id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->pi_id,
                    'product_reference_id' => $row->pi_product,
                    'customer_id' => $row->pi_client ?? null,
                    'license_code' => $row->pi_license_code,
                    'url' => $row->pi_url ?? null,
                    'ip_address' => $row->pi_ip ?? null,
                    'activated_at' => $row->pi_date ?? null,
                    'user_agent' => $row->pi_agent ?? null,
                    'is_valid' => $row->pi_isvalid ?? 1,
                    'is_active' => $row->pi_isactive ?? 1,
                    'created_at' => $row->pi_date ?? Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_activations')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_activations')->count() . ' activations');
    }

    protected function migrateActivityLogs(): void
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasTable('lm_activity_logs')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('activity_logs', 'al_id')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_activity_logs')->count() > 0) {
            return;
        }

        DB::table('activity_logs')->orderBy('al_id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->al_id,
                    'type' => null,
                    'message' => $row->al_log,
                    'created_at' => $row->al_date ?? Carbon::now(),
                    'updated_at' => $row->al_date ?? Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_activity_logs')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_activity_logs')->count() . ' activity logs');
    }

    protected function migrateUpdateDownloads(): void
    {
        if (! Schema::hasTable('update_downloads') || ! Schema::hasTable('lm_update_downloads')) {
            return;
        }

        // Skip if legacy columns don't exist (already modernized)
        if (! Schema::hasColumn('update_downloads', 'did')) {
            return;
        }

        // Skip if already migrated
        if (DB::table('lm_update_downloads')->count() > 0) {
            return;
        }

        DB::table('update_downloads')->orderBy('id')->chunk(500, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => $row->id,
                    'download_id' => $row->did ?? null,
                    'product_reference_id' => $row->product,
                    'version_id' => $row->vid ?? null,
                    'url' => $row->url ?? null,
                    'ip_address' => $row->ip ?? null,
                    'is_valid' => $row->isvalid ?? 1,
                    'downloaded_at' => $row->download_date ?? null,
                    'created_at' => $row->download_date ?? Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            if (! empty($data)) {
                DB::table('lm_update_downloads')->insert($data);
            }
        });

        $this->log('Migrated ' . DB::table('lm_update_downloads')->count() . ' update downloads');
    }

    protected function log(string $message): void
    {
        if (app()->runningInConsole()) {
            echo $message . "\n";
        }
    }

    public function down(): void
    {
        // Truncate new tables (reversible only if old tables still exist)
        $tables = [
            'lm_products',
            'lm_product_versions',
            'lm_licenses',
            'lm_activations',
            'lm_activity_logs',
            'lm_update_downloads',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }
};
