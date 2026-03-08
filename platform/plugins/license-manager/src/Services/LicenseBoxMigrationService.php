<?php

namespace Botble\LicenseManager\Services;

use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class LicenseBoxMigrationService
{
    // Core data tables
    protected array $tables = [
        'product_details',
        'product_licenses',
        'product_activations',
        'product_versions',
    ];

    // Additional legacy tables to detect and delete
    protected array $additionalTables = [
        'activity_logs',
        'api_keys',
        'api_limits',
        'api_logs',
        'app_settings',
        'auth_users',
        'cron_mails',
        'update_downloads',
    ];

    public function detectTables(): array
    {
        $found = [];

        foreach (array_merge($this->tables, $this->additionalTables) as $table) {
            $found[$table] = Schema::hasTable($table);
        }

        return $found;
    }

    public function hasLegacyTables(): bool
    {
        foreach (array_merge($this->tables, $this->additionalTables) as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    public function getRecordCounts(): array
    {
        $counts = [];

        foreach (array_merge($this->tables, $this->additionalTables) as $table) {
            $counts[$table] = Schema::hasTable($table)
                ? DB::table($table)->count()
                : 0;
        }

        return $counts;
    }

    public function migrateStep(string $step, int $offset = 0, int $limit = 100): array
    {
        $steps = ['products', 'customers', 'licenses', 'activations', 'versions', 'activity_logs', 'update_downloads', 'api_keys', 'settings'];
        $currentIndex = array_search($step, $steps);
        $nextStep = $steps[$currentIndex + 1] ?? null;

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                $result = match ($step) {
                    'products' => $this->migrateProductsPaginated($offset, $limit),
                    'customers' => $this->migrateCustomersPaginated($offset, $limit),
                    'licenses' => $this->migrateLicensesPaginated($offset, $limit),
                    'activations' => $this->migrateActivationsPaginated($offset, $limit),
                    'versions' => $this->migrateVersionsPaginated($offset, $limit),
                    'activity_logs' => $this->migrateActivityLogsPaginated($offset, $limit),
                    'update_downloads' => $this->migrateUpdateDownloadsPaginated($offset, $limit),
                    'api_keys' => $this->migrateApiKeysPaginated($offset, $limit),
                    'settings' => $this->migrateSettingsPaginated($offset, $limit),
                    default => ['count' => 0, 'has_more' => false],
                };

                return [
                    'success' => true,
                    'step' => $step,
                    'count' => $result['count'],
                    'offset' => $offset,
                    'has_more' => $result['has_more'],
                    'next_offset' => $result['has_more'] ? $offset + $limit : 0,
                    'next_step' => $result['has_more'] ? null : $nextStep,
                    'step_completed' => ! $result['has_more'],
                    'completed' => ! $result['has_more'] && $nextStep === null,
                ];
            } catch (Throwable $e) {
                // Retry on deadlock
                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'Deadlock')) {
                    usleep(100000 * $attempt);

                    continue;
                }

                return [
                    'success' => false,
                    'step' => $step,
                    'error' => $e->getMessage(),
                    'message' => trans('plugins/license-manager::license-manager.legacy_migration.step_failed', [
                        'step' => $step,
                    ]),
                ];
            }
        }

        return [
            'success' => false,
            'step' => $step,
            'error' => trans('plugins/license-manager::license-manager.legacy_migration.max_retries_exceeded'),
        ];
    }

    protected function migrateProductsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('product_details')) {
            return ['count' => 0, 'has_more' => false];
        }

        $products = DB::table('product_details')
            ->orderBy('pd_id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($products as $product) {
            $created = Product::query()->firstOrCreate(
                ['reference_id' => $product->pd_pid],
                [
                    'envato_id' => $product->envato_id,
                    'name' => $product->pd_name,
                    'description' => $product->pd_details,
                    'license_update' => (bool) $product->license_update,
                    'serve_latest_updates' => (bool) $product->serve_latest_updates,
                    'is_active' => (bool) $product->pd_status,
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return ['count' => $count, 'has_more' => $products->count() === $limit];
    }

    protected function migrateCustomersPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('product_licenses')) {
            return ['count' => 0, 'has_more' => false];
        }

        $existingClientIds = Customer::query()->whereNotNull('client_id')->pluck('client_id')->flip()->toArray();
        $existingEmails = Customer::query()->pluck('email')->flip()->toArray();

        // Use GROUP BY to get unique clients (DISTINCT on client,email returns duplicates)
        $clients = DB::table('product_licenses')
            ->select('client', DB::raw('MAX(email) as email'))
            ->whereNotNull('client')
            ->where('client', '!=', '')
            ->groupBy('client')
            ->orderBy('client')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        $password = bcrypt(Str::random(16));

        foreach ($clients as $client) {
            if (isset($existingClientIds[$client->client])) {
                continue;
            }

            // Skip clients without real email - old LicenseBox customers couldn't login anyway
            $email = $client->email;
            if (empty($email)) {
                continue;
            }

            // Skip if email already exists
            if (isset($existingEmails[$email])) {
                continue;
            }

            try {
                Customer::query()->create([
                    'client_id' => $client->client,
                    'name' => $client->client,
                    'email' => $email,
                    'password' => $password,
                ]);

                $existingClientIds[$client->client] = true;
                $existingEmails[$email] = true;
                $count++;
            } catch (Throwable) {
                // Skip if duplicate (race condition)
                continue;
            }
        }

        return ['count' => $count, 'has_more' => $clients->count() === $limit];
    }

    protected function migrateLicensesPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('product_licenses')) {
            return ['count' => 0, 'has_more' => false];
        }

        $licenses = DB::table('product_licenses')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($licenses as $license) {
            $created = ProductLicense::query()->firstOrCreate(
                ['license_code' => $license->license_code],
                [
                    'product_reference_id' => $license->pid,
                    'type' => $license->license_type,
                    'invoice' => $license->invoice,
                    'is_envato' => (bool) ($license->is_envato ?? false),
                    'customer_id' => $license->client ?: null,
                    'email' => $license->email,
                    'uses' => $license->uses ?? 0,
                    'parallel_uses' => $license->parallel_uses,
                    'expires_at' => $this->parseDate($license->expiry ?? null),
                    'expiry_days' => $license->expiry_days,
                    'updates_until' => $this->parseDate($license->updates_till ?? null),
                    'support_until' => $this->parseDate($license->supported_till ?? null),
                    'domains' => $this->parseListToJson($license->domains ?? null),
                    'ips' => $this->parseListToJson($license->ips ?? null),
                    'comments' => $license->comments,
                    'is_valid' => ! (bool) ($license->validity ?? false),
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return ['count' => $count, 'has_more' => $licenses->count() === $limit];
    }

    protected function migrateActivationsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('product_activations')) {
            return ['count' => 0, 'has_more' => false];
        }

        $activations = DB::table('product_activations')
            ->orderBy('pi_id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($activations as $activation) {
            $exists = ProductActivation::query()
                ->where('license_code', $activation->pi_license_code)
                ->where('url', $activation->pi_url)
                ->where('ip_address', $activation->pi_ip)
                ->exists();

            if ($exists) {
                continue;
            }

            ProductActivation::query()->create([
                'product_reference_id' => $activation->pi_product,
                'customer_id' => $activation->pi_client ?: null,
                'license_code' => $activation->pi_license_code,
                'url' => $activation->pi_url,
                'ip_address' => $activation->pi_ip,
                'activated_at' => $this->parseDate($activation->pi_date ?? null),
                'user_agent' => $activation->pi_agent ?? null,
                'is_valid' => (bool) ($activation->pi_isvalid ?? true),
                'is_active' => (bool) ($activation->pi_isactive ?? true),
            ]);

            $count++;
        }

        return ['count' => $count, 'has_more' => $activations->count() === $limit];
    }

    protected function migrateVersionsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('product_versions')) {
            return ['count' => 0, 'has_more' => false];
        }

        $versions = DB::table('product_versions')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($versions as $version) {
            $created = ProductVersion::query()->firstOrCreate(
                [
                    'version_id' => $version->vid,
                    'product_reference_id' => $version->pid,
                ],
                [
                    'version' => $version->version,
                    'released_at' => $this->parseDate($version->release_date ?? null),
                    'summary' => $version->summary,
                    'changelog' => $version->changelog,
                    'main_file' => $version->main_file,
                    'sql_file' => $version->sql_file,
                    'is_active' => (bool) ($version->status ?? true),
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return ['count' => $count, 'has_more' => $versions->count() === $limit];
    }

    protected function migrateActivityLogsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasTable('lm_activity_logs')) {
            return ['count' => 0, 'has_more' => false];
        }

        // Skip if legacy columns don't exist
        if (! Schema::hasColumn('activity_logs', 'al_id')) {
            return ['count' => 0, 'has_more' => false];
        }

        $logs = DB::table('activity_logs')
            ->orderBy('al_id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($logs as $log) {
            // Check if already migrated
            if (DB::table('lm_activity_logs')->where('id', $log->al_id)->exists()) {
                continue;
            }

            DB::table('lm_activity_logs')->insert([
                'id' => $log->al_id,
                'type' => null,
                'message' => $log->al_log,
                'created_at' => $log->al_date ?? Carbon::now(),
                'updated_at' => $log->al_date ?? Carbon::now(),
            ]);

            $count++;
        }

        return ['count' => $count, 'has_more' => $logs->count() === $limit];
    }

    protected function migrateUpdateDownloadsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('update_downloads') || ! Schema::hasTable('lm_update_downloads')) {
            return ['count' => 0, 'has_more' => false];
        }

        // Skip if legacy columns don't exist
        if (! Schema::hasColumn('update_downloads', 'did')) {
            return ['count' => 0, 'has_more' => false];
        }

        $downloads = DB::table('update_downloads')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($downloads as $download) {
            // Check if already migrated
            if (DB::table('lm_update_downloads')->where('id', $download->id)->exists()) {
                continue;
            }

            DB::table('lm_update_downloads')->insert([
                'id' => $download->id,
                'download_id' => $download->did ?? null,
                'product_reference_id' => $download->product,
                'version_id' => $download->vid ?? null,
                'url' => $download->url ?? null,
                'ip_address' => $download->ip ?? null,
                'is_valid' => $download->isvalid ?? 1,
                'downloaded_at' => $download->download_date ?? null,
                'created_at' => $download->download_date ?? Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $count++;
        }

        return ['count' => $count, 'has_more' => $downloads->count() === $limit];
    }

    protected function migrateApiKeysPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('api_keys')) {
            return ['count' => 0, 'has_more' => false];
        }

        $apiKeys = DB::table('api_keys')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $count = 0;

        // Map legacy endpoints to new scopes
        $externalScopes = [
            'connection:check',
            'license:activate',
            'license:deactivate',
            'license:verify',
            'license:check',
            'update:list',
            'update:latest',
            'update:check',
            'update:download',
        ];

        $internalScopes = [
            'connection:check',
            'products:list',
            'products:create',
            'products:read',
            'products:update',
            'products:delete',
            'products:activate',
            'products:deactivate',
            'versions:list',
            'versions:create',
            'versions:read',
            'versions:update',
            'licenses:list',
            'licenses:create',
            'licenses:read',
            'licenses:update',
            'licenses:block',
            'licenses:unblock',
            'activations:list',
            'activations:activate',
            'activations:deactivate',
        ];

        foreach ($apiKeys as $apiKey) {
            // Check if key already exists
            if (ApiKey::query()->where('key', $apiKey->key)->exists()) {
                continue;
            }

            $isInternal = str_contains($apiKey->controller ?? '', 'internal');
            $type = $isInternal ? ApiKeyType::Internal : ApiKeyType::External;
            $scopes = $isInternal ? $internalScopes : $externalScopes;

            ApiKey::query()->create([
                'type' => $type,
                'key' => $apiKey->key,
                'scopes' => $scopes,
                'special' => (bool) ($apiKey->ignore_limits ?? false),
                'revoked' => false,
            ]);

            $count++;
        }

        return ['count' => $count, 'has_more' => $apiKeys->count() === $limit];
    }

    protected function migrateSettingsPaginated(int $offset, int $limit): array
    {
        if (! Schema::hasTable('app_settings')) {
            return ['count' => 0, 'has_more' => false];
        }

        // Settings migration is not paginated - just do it all at once
        if ($offset > 0) {
            return ['count' => 0, 'has_more' => false];
        }

        $settings = DB::table('app_settings')->pluck('as_value', 'as_name');
        $count = 0;

        // Map legacy settings to new settings keys
        $settingsMap = [
            'blacklisted_ips' => 'lm_blacklisted_ips',
            'blacklisted_domains' => 'lm_blacklisted_domains',
            'api_rate_limit' => 'lm_requests_rate_limiting_period',
            'api_rate_limit_method' => 'lm_requests_rate_limiting_method',
            'failed_activation_logs' => 'lm_add_entries_for_failed_activation_attempts',
            'failed_update_download_logs' => 'lm_add_entries_for_failed_update_download_attempts',
            'auto_domain_blacklist' => 'lm_blacklist_domain_after_failed_attempts',
            'auto_ip_blacklist' => 'lm_blacklist_ip_after_failed_attempts',
            'auto_deactivate_activations' => 'lm_deactivate_old_activations_on_new_activation',
            'auto_add_licensed_domain' => 'lm_add_domain_of_first_activation_as_licensed_domain',
            'envato_parallel_use_limit' => 'lm_default_envato_parallel_uses_limit',
        ];

        foreach ($settingsMap as $legacyKey => $newKey) {
            if (! isset($settings[$legacyKey])) {
                continue;
            }

            $value = $settings[$legacyKey];

            // Skip empty values
            if ($value === '' || $value === null) {
                continue;
            }

            // Skip if already set in new system
            if (Setting::get($newKey) !== null) {
                continue;
            }

            // Handle blacklists - convert to JSON array format
            if (in_array($legacyKey, ['blacklisted_ips', 'blacklisted_domains'])) {
                $items = array_filter(array_map('trim', explode(',', $value)));
                $value = json_encode(array_map(fn ($item) => ['value' => $item], $items));
            }

            // Handle rate limit method mapping
            if ($legacyKey === 'api_rate_limit_method') {
                $value = match ($value) {
                    'api_key' => 'api_key',
                    'api_key_ip' => 'api_key_ip',
                    default => 'ip_address',
                };
            }

            Setting::set($newKey, $value);
            $count++;
        }

        if ($count > 0) {
            Setting::save();
        }

        return ['count' => $count, 'has_more' => false];
    }

    public function migrate(): MigrationResult
    {
        $errors = [];
        $productsCreated = 0;
        $customersCreated = 0;
        $licensesCreated = 0;
        $activationsCreated = 0;
        $versionsCreated = 0;

        try {
            DB::beginTransaction();

            $productsCreated = $this->migrateProducts();
            $customersCreated = $this->migrateCustomers();
            $licensesCreated = $this->migrateLicenses();
            $activationsCreated = $this->migrateActivations();
            $versionsCreated = $this->migrateVersions();

            DB::commit();

            return new MigrationResult(
                success: true,
                productsCreated: $productsCreated,
                customersCreated: $customersCreated,
                licensesCreated: $licensesCreated,
                activationsCreated: $activationsCreated,
                versionsCreated: $versionsCreated,
                message: trans('plugins/license-manager::license-manager.legacy_migration.migration_completed'),
            );
        } catch (Throwable $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();

            return new MigrationResult(
                success: false,
                errors: $errors,
                message: trans('plugins/license-manager::license-manager.legacy_migration.migration_failed'),
            );
        }
    }

    public function deleteLegacyTables(): bool
    {
        try {
            foreach (array_merge($this->tables, $this->additionalTables) as $table) {
                if (Schema::hasTable($table)) {
                    Schema::drop($table);
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    protected function migrateProducts(): int
    {
        if (! Schema::hasTable('product_details')) {
            return 0;
        }

        $count = 0;

        DB::table('product_details')
            ->orderBy('pd_id')
            ->chunk(100, function ($products) use (&$count): void {
                foreach ($products as $product) {
                    $created = Product::query()->firstOrCreate(
                        ['reference_id' => $product->pd_pid],
                        [
                            'envato_id' => $product->envato_id,
                            'name' => $product->pd_name,
                            'description' => $product->pd_details,
                            'license_update' => (bool) $product->license_update,
                            'serve_latest_updates' => (bool) $product->serve_latest_updates,
                            'is_active' => (bool) $product->pd_status,
                        ]
                    );

                    if ($created->wasRecentlyCreated) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    protected function migrateCustomers(): int
    {
        if (! Schema::hasTable('product_licenses')) {
            return 0;
        }

        // Pre-load existing client_ids and emails for faster lookups
        $existingClientIds = Customer::query()->whereNotNull('client_id')->pluck('client_id')->flip()->toArray();
        $existingEmails = Customer::query()->pluck('email')->flip()->toArray();

        $count = 0;
        $password = bcrypt(Str::random(16)); // Use same password for all legacy imports

        DB::table('product_licenses')
            ->select('client', 'email')
            ->whereNotNull('client')
            ->where('client', '!=', '')
            ->distinct()
            ->orderBy('client')
            ->chunk(50, function ($clients) use (&$count, &$existingClientIds, &$existingEmails, $password): void {
                foreach ($clients as $client) {
                    // Skip if client_id already exists
                    if (isset($existingClientIds[$client->client])) {
                        continue;
                    }

                    $email = $client->email ?: Str::slug($client->client) . '@legacy.local';

                    // Skip if email already exists
                    if (isset($existingEmails[$email])) {
                        continue;
                    }

                    // Create new customer
                    Customer::query()->create([
                        'client_id' => $client->client,
                        'name' => $client->client,
                        'email' => $email,
                        'password' => $password,
                    ]);

                    // Add to cache to prevent duplicates in same batch
                    $existingClientIds[$client->client] = true;
                    $existingEmails[$email] = true;

                    $count++;
                }
            });

        return $count;
    }

    protected function migrateLicenses(): int
    {
        if (! Schema::hasTable('product_licenses')) {
            return 0;
        }

        $count = 0;

        DB::table('product_licenses')
            ->orderBy('id')
            ->chunk(100, function ($licenses) use (&$count): void {
                foreach ($licenses as $license) {
                    $created = ProductLicense::query()->firstOrCreate(
                        ['license_code' => $license->license_code],
                        [
                            'product_reference_id' => $license->pid,
                            'type' => $license->license_type,
                            'invoice' => $license->invoice,
                            'is_envato' => (bool) ($license->is_envato ?? false),
                            'customer_id' => $license->client ?: null,
                            'email' => $license->email,
                            'uses' => $license->uses ?? 0,
                            'parallel_uses' => $license->parallel_uses,
                            'expires_at' => $this->parseDate($license->expiry ?? null),
                            'expiry_days' => $license->expiry_days,
                            'updates_until' => $this->parseDate($license->updates_till ?? null),
                            'support_until' => $this->parseDate($license->supported_till ?? null),
                            'domains' => $this->parseListToJson($license->domains ?? null),
                            'ips' => $this->parseListToJson($license->ips ?? null),
                            'comments' => $license->comments,
                            'is_valid' => ! (bool) ($license->validity ?? false),
                        ]
                    );

                    if ($created->wasRecentlyCreated) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    protected function migrateActivations(): int
    {
        if (! Schema::hasTable('product_activations')) {
            return 0;
        }

        $count = 0;

        DB::table('product_activations')
            ->orderBy('pi_id')
            ->chunk(100, function ($activations) use (&$count): void {
                foreach ($activations as $activation) {
                    $existingActivation = ProductActivation::query()
                        ->where('license_code', $activation->pi_license_code)
                        ->where('url', $activation->pi_url)
                        ->where('ip_address', $activation->pi_ip)
                        ->first();

                    if ($existingActivation) {
                        continue;
                    }

                    ProductActivation::query()->create([
                        'product_reference_id' => $activation->pi_product,
                        'customer_id' => $activation->pi_client ?: null,
                        'license_code' => $activation->pi_license_code,
                        'url' => $activation->pi_url,
                        'ip_address' => $activation->pi_ip,
                        'activated_at' => $this->parseDate($activation->pi_date ?? null),
                        'user_agent' => $activation->pi_agent ?? null,
                        'is_valid' => (bool) ($activation->pi_isvalid ?? true),
                        'is_active' => (bool) ($activation->pi_isactive ?? true),
                    ]);

                    $count++;
                }
            });

        return $count;
    }

    protected function migrateVersions(): int
    {
        if (! Schema::hasTable('product_versions')) {
            return 0;
        }

        $count = 0;

        DB::table('product_versions')
            ->orderBy('id')
            ->chunk(100, function ($versions) use (&$count): void {
                foreach ($versions as $version) {
                    $created = ProductVersion::query()->firstOrCreate(
                        [
                            'version_id' => $version->vid,
                            'product_reference_id' => $version->pid,
                        ],
                        [
                            'version' => $version->version,
                            'released_at' => $this->parseDate($version->release_date ?? null),
                            'summary' => $version->summary,
                            'changelog' => $version->changelog,
                            'main_file' => $version->main_file,
                            'sql_file' => $version->sql_file,
                            'is_active' => (bool) ($version->status ?? true),
                        ]
                    );

                    if ($created->wasRecentlyCreated) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    protected function parseListToJson(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $items = preg_split('/[,\n]+/', $value);
        $items = array_map('trim', $items);
        $items = array_filter($items);

        return empty($items) ? null : array_values($items);
    }

    protected function parseDate(mixed $value): ?string
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        return $value;
    }
}
