<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    protected array $settingsMap = [
        'api_rate_limit_method' => 'ls_requests_rate_limiting_method',
        'api_rate_limit' => 'ls_requests_rate_limiting_period',
        'auto_domain_blacklist' => 'ls_blacklist_domain_after_failed_attempts',
        'auto_ip_blacklist' => 'ls_blacklist_ip_after_failed_attempts',
        'blacklisted_ips' => 'ls_blacklisted_ips',
        'blacklisted_domains' => 'ls_blacklisted_domains',
        'failed_activation_logs' => 'ls_add_entries_for_failed_activation_attempts',
        'failed_update_download_logs' => 'ls_add_entries_for_failed_update_download_attempts',
        'auto_deactivate_activations' => 'ls_deactivate_old_activations_on_new_activation',
        'auto_add_licensed_domain' => 'ls_add_domain_of_first_activation_as_licensed_domain',
        'envato_use_limit' => 'ls_default_envato_license_uses_limit',
        'envato_parallel_use_limit' => 'ls_default_envato_parallel_uses_limit',
        'license_expiring_enable' => 'ls_send_expiration_warnings',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        $appSettings = DB::table('app_settings')->pluck('as_value', 'as_name');

        if ($appSettings->isEmpty()) {
            return;
        }

        $settingsToSave = [];

        foreach ($this->settingsMap as $lbKey => $lsKey) {
            $value = $appSettings->get($lbKey);

            if ($value === null || $value === '') {
                continue;
            }

            $convertedValue = $this->convertValue($lbKey, $value);

            if ($convertedValue !== null) {
                $settingsToSave[$lsKey] = $convertedValue;
            }
        }

        if (! empty($settingsToSave)) {
            setting()->set($settingsToSave)->save();
        }

        $this->migrateLicenseCodeFormat($appSettings);
    }

    protected function migrateLicenseCodeFormat(mixed $appSettings): void
    {
        $value = $appSettings->get('license_code_format');

        if ($value === null || $value === '') {
            return;
        }

        if (in_array($value, ['uuid', 'ulid', 'random', 'custom'], true)) {
            setting()->set('ls_license_code_format', $value)->save();

            return;
        }

        $customFormat = preg_replace('/\{\[([A-Z])\]\}/', '%$1', $value) ?: $value;

        setting()->set([
            'ls_license_code_format' => 'custom',
            'ls_license_code_custom_format' => $customFormat,
        ])->save();
    }

    public function down(): void
    {
        $settingKeys = array_values($this->settingsMap);

        DB::table('settings')
            ->whereIn('key', $settingKeys)
            ->delete();
    }

    protected function convertValue(string $key, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($key) {
            'blacklisted_ips', 'blacklisted_domains' => $this->convertToJsonArray($value),
            'api_rate_limit_method' => $this->convertRateLimitMethod($value),
            default => $value,
        };
    }

    protected function convertToJsonArray(string $value): string
    {
        if (empty($value)) {
            return '[]';
        }

        $items = array_filter(array_map('trim', explode(',', $value)));

        return json_encode(array_values($items));
    }

    protected function convertRateLimitMethod(string $value): string
    {
        return match (strtolower($value)) {
            'api_key' => 'api_key',
            default => 'ip_address',
        };
    }
};
