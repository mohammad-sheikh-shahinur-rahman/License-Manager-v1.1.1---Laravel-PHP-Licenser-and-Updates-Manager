<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class () extends Migration {
    /**
     * Mapping of ls_* keys to lm_* keys.
     */
    protected array $keyMapping = [
        // Encryption settings
        'ls_encryption_key' => 'lm_license_encryption_key',
        'ls_encryption_cipher' => 'lm_license_encryption_cipher',
        'ls_legacy_encryption_key' => 'lm_legacy_encryption_key',
        'ls_license_encryption_key_regenerate' => 'lm_license_encryption_key_regenerate',

        // Rate limiting settings
        'ls_requests_rate_limiting_period' => 'lm_requests_rate_limiting_period',
        'ls_requests_rate_limiting_method' => 'lm_requests_rate_limiting_method',

        // License activation settings
        'ls_add_domain_of_first_activation_as_licensed_domain' => 'lm_add_domain_of_first_activation_as_licensed_domain',
        'ls_deactivate_old_activations_on_new_activation' => 'lm_deactivate_old_activations_on_new_activation',
        'ls_add_entries_for_failed_activation_attempts' => 'lm_add_entries_for_failed_activation_attempts',
        'ls_verify_license_ip' => 'lm_verify_license_ip',

        // Site branding settings
        'ls_site_title' => 'lm_site_title',
        'ls_site_logo' => 'lm_site_logo',
        'ls_site_favicon' => 'lm_site_favicon',
        'ls_footer_text' => 'lm_footer_text',

        // Blacklist settings
        'ls_blacklisted_buyer_names' => 'lm_blacklisted_buyer_names',
    ];

    public function up(): void
    {
        foreach ($this->keyMapping as $oldKey => $newKey) {
            $setting = DB::table('settings')->where('key', $oldKey)->first();

            if ($setting) {
                $exists = DB::table('settings')->where('key', $newKey)->exists();

                if (! $exists) {
                    DB::table('settings')->insert([
                        'id' => Str::uuid()->toString(),
                        'key' => $newKey,
                        'value' => $setting->value,
                    ]);
                }

                DB::table('settings')->where('key', $oldKey)->delete();
            }
        }
    }

    public function down(): void
    {
        foreach ($this->keyMapping as $oldKey => $newKey) {
            $setting = DB::table('settings')->where('key', $newKey)->first();

            if ($setting) {
                $exists = DB::table('settings')->where('key', $oldKey)->exists();

                if (! $exists) {
                    DB::table('settings')->insert([
                        'id' => Str::uuid()->toString(),
                        'key' => $oldKey,
                        'value' => $setting->value,
                    ]);
                }
            }
        }
    }
};
