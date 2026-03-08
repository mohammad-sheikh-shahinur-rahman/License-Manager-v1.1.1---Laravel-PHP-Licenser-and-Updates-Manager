<?php

use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    protected string $legacyEncryptionKey = '1af0f4bdeb9ac1ed8360';

    protected array $externalScopes = [
        'connection:check',
        'license:activate',
        'license:deactivate',
        'license:verify',
        'update:list',
        'update:latest',
        'update:check',
        'update:download',
        'license:check',
    ];

    protected array $internalScopes = [
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

    public function up(): void
    {
        if (! setting('ls_legacy_encryption_key')) {
            Setting::set('ls_legacy_encryption_key', $this->legacyEncryptionKey);
            Setting::save();
        }

        $apiKeysTable = Schema::hasTable('lm_api_keys') ? 'lm_api_keys' : 'ls_api_keys';

        if (Schema::hasTable('api_keys') && Schema::hasColumn('api_keys', 'controller') && Schema::hasTable($apiKeysTable)) {
            $oldApiKeys = DB::table('api_keys')->get();

            foreach ($oldApiKeys as $oldKey) {
                if (DB::table($apiKeysTable)->where('key', $oldKey->key)->exists()) {
                    continue;
                }

                $isInternal = str_contains($oldKey->controller, 'internal');

                DB::table($apiKeysTable)->insert([
                    'id' => Str::uuid()->toString(),
                    'type' => $isInternal ? 'internal' : 'external',
                    'key' => $oldKey->key,
                    'scopes' => json_encode($isInternal ? $this->internalScopes : $this->externalScopes),
                    'special' => (bool) $oldKey->ignore_limits,
                    'revoked' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Setting::delete('ls_legacy_encryption_key');
        Setting::save();
    }
};
