<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $tablesToRename = [
            'ls_customers' => 'lm_customers',
            'ls_customer_password_reset_tokens' => 'lm_customer_password_reset_tokens',
            'ls_api_keys' => 'lm_api_keys',
        ];

        foreach ($tablesToRename as $oldName => $newName) {
            if (Schema::hasTable($oldName) && ! Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
            }
        }

        $settingsToRename = [
            'ls_license_code_format' => 'lm_license_code_format',
            'ls_license_code_case_insensitive' => 'lm_license_code_case_insensitive',
            'ls_license_code_random_length' => 'lm_license_code_random_length',
            'ls_license_code_custom_format' => 'lm_license_code_custom_format',
            'ls_license_encryption_cipher' => 'lm_license_encryption_cipher',
            'ls_license_encryption_key' => 'lm_license_encryption_key',
            'ls_product_unique_id_format' => 'lm_product_unique_id_format',
            'ls_product_version_unique_id_format' => 'lm_product_version_unique_id_format',
            'ls_blacklist_domain_after_failed_attempts' => 'lm_blacklist_domain_after_failed_attempts',
            'ls_blacklist_ip_after_failed_attempts' => 'lm_blacklist_ip_after_failed_attempts',
            'ls_requests_rate_limiting_method' => 'lm_requests_rate_limiting_method',
            'ls_requests_rate_limiting_period' => 'lm_requests_rate_limiting_period',
            'ls_blacklisted_domains' => 'lm_blacklisted_domains',
            'ls_blacklisted_ips' => 'lm_blacklisted_ips',
            'ls_blacklisted_buyers' => 'lm_blacklisted_buyers',
            'ls_blacklisted_license_codes' => 'lm_blacklisted_license_codes',
            'ls_legacy_encryption_key' => 'lm_legacy_encryption_key',
            'ls_envato_client_id' => 'lm_envato_client_id',
            'ls_envato_client_secret' => 'lm_envato_client_secret',
            'ls_envato_owner_username' => 'lm_envato_owner_username',
            'ls_envato_marketplace' => 'lm_envato_marketplace',
            'ls_default_envato_license_uses_limit' => 'lm_default_envato_license_uses_limit',
            'ls_default_envato_parallel_uses_limit' => 'lm_default_envato_parallel_uses_limit',
            'ls_send_expiration_warnings' => 'lm_send_expiration_warnings',
            'ls_expiration_warning_days' => 'lm_expiration_warning_days',
            'ls_enable_webhooks' => 'lm_enable_webhooks',
            'ls_webhook_url' => 'lm_webhook_url',
            'ls_webhook_secret' => 'lm_webhook_secret',
            'ls_enable_public_verification' => 'lm_enable_public_verification',
            'ls_website_logo' => 'lm_website_logo',
            'ls_website_favicon' => 'lm_website_favicon',
            'ls_website_title' => 'lm_website_title',
            'ls_website_copyright_text' => 'lm_website_copyright_text',
            'ls_website_login_screen_backgrounds' => 'lm_website_login_screen_backgrounds',
            'ls_primary_font' => 'lm_primary_font',
            'ls_primary_color' => 'lm_primary_color',
            'ls_secondary_color' => 'lm_secondary_color',
            'ls_heading_color' => 'lm_heading_color',
            'ls_text_color' => 'lm_text_color',
            'ls_link_color' => 'lm_link_color',
            'ls_link_hover_color' => 'lm_link_hover_color',
            'ls_add_entries_for_failed_activation_attempts' => 'lm_add_entries_for_failed_activation_attempts',
            'ls_add_entries_for_failed_update_download_attempts' => 'lm_add_entries_for_failed_update_download_attempts',
            'ls_deactivate_old_activations_on_new_activation' => 'lm_deactivate_old_activations_on_new_activation',
            'ls_add_domain_of_first_activation_as_licensed_domain' => 'lm_add_domain_of_first_activation_as_licensed_domain',
            'ls_customer_recaptcha_enabled' => 'lm_customer_recaptcha_enabled',
            'ls_customer_math_captcha_enabled' => 'lm_customer_math_captcha_enabled',
        ];

        foreach ($settingsToRename as $oldKey => $newKey) {
            // Skip if new key already exists (migration already applied)
            if (DB::table('settings')->where('key', $newKey)->exists()) {
                continue;
            }

            DB::table('settings')
                ->where('key', $oldKey)
                ->update(['key' => $newKey]);
        }
    }

    public function down(): void
    {
        $tablesToRename = [
            'lm_customers' => 'ls_customers',
            'lm_customer_password_reset_tokens' => 'ls_customer_password_reset_tokens',
            'lm_api_keys' => 'ls_api_keys',
        ];

        foreach ($tablesToRename as $oldName => $newName) {
            if (Schema::hasTable($oldName) && ! Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
            }
        }

        $settingsToRename = [
            'lm_license_code_format' => 'ls_license_code_format',
            'lm_license_code_case_insensitive' => 'ls_license_code_case_insensitive',
            'lm_license_code_random_length' => 'ls_license_code_random_length',
            'lm_license_code_custom_format' => 'ls_license_code_custom_format',
            'lm_license_encryption_cipher' => 'ls_license_encryption_cipher',
            'lm_license_encryption_key' => 'ls_license_encryption_key',
            'lm_product_unique_id_format' => 'ls_product_unique_id_format',
            'lm_product_version_unique_id_format' => 'ls_product_version_unique_id_format',
            'lm_blacklist_domain_after_failed_attempts' => 'ls_blacklist_domain_after_failed_attempts',
            'lm_blacklist_ip_after_failed_attempts' => 'ls_blacklist_ip_after_failed_attempts',
            'lm_requests_rate_limiting_method' => 'ls_requests_rate_limiting_method',
            'lm_requests_rate_limiting_period' => 'ls_requests_rate_limiting_period',
            'lm_blacklisted_domains' => 'ls_blacklisted_domains',
            'lm_blacklisted_ips' => 'ls_blacklisted_ips',
            'lm_blacklisted_buyers' => 'ls_blacklisted_buyers',
            'lm_blacklisted_license_codes' => 'ls_blacklisted_license_codes',
            'lm_legacy_encryption_key' => 'ls_legacy_encryption_key',
            'lm_envato_client_id' => 'ls_envato_client_id',
            'lm_envato_client_secret' => 'ls_envato_client_secret',
            'lm_envato_owner_username' => 'ls_envato_owner_username',
            'lm_envato_marketplace' => 'ls_envato_marketplace',
            'lm_default_envato_license_uses_limit' => 'ls_default_envato_license_uses_limit',
            'lm_default_envato_parallel_uses_limit' => 'ls_default_envato_parallel_uses_limit',
            'lm_send_expiration_warnings' => 'ls_send_expiration_warnings',
            'lm_expiration_warning_days' => 'ls_expiration_warning_days',
            'lm_enable_webhooks' => 'ls_enable_webhooks',
            'lm_webhook_url' => 'ls_webhook_url',
            'lm_webhook_secret' => 'ls_webhook_secret',
            'lm_enable_public_verification' => 'ls_enable_public_verification',
            'lm_website_logo' => 'ls_website_logo',
            'lm_website_favicon' => 'ls_website_favicon',
            'lm_website_title' => 'ls_website_title',
            'lm_website_copyright_text' => 'ls_website_copyright_text',
            'lm_website_login_screen_backgrounds' => 'ls_website_login_screen_backgrounds',
            'lm_primary_font' => 'ls_primary_font',
            'lm_primary_color' => 'ls_primary_color',
            'lm_secondary_color' => 'ls_secondary_color',
            'lm_heading_color' => 'ls_heading_color',
            'lm_text_color' => 'ls_text_color',
            'lm_link_color' => 'ls_link_color',
            'lm_link_hover_color' => 'ls_link_hover_color',
            'lm_add_entries_for_failed_activation_attempts' => 'ls_add_entries_for_failed_activation_attempts',
            'lm_add_entries_for_failed_update_download_attempts' => 'ls_add_entries_for_failed_update_download_attempts',
            'lm_deactivate_old_activations_on_new_activation' => 'ls_deactivate_old_activations_on_new_activation',
            'lm_add_domain_of_first_activation_as_licensed_domain' => 'ls_add_domain_of_first_activation_as_licensed_domain',
            'lm_customer_recaptcha_enabled' => 'ls_customer_recaptcha_enabled',
            'lm_customer_math_captcha_enabled' => 'ls_customer_math_captcha_enabled',
        ];

        foreach ($settingsToRename as $oldKey => $newKey) {
            DB::table('settings')
                ->where('key', $oldKey)
                ->update(['key' => $newKey]);
        }
    }
};
