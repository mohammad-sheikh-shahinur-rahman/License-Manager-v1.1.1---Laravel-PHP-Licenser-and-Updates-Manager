<?php

namespace Botble\LicenseManager\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\LicenseManager\Enums\EnvatoSite;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class GeneralSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'lm_license_code_format' => ['required', 'string', 'max:255', 'in:uuid,ulid,random,custom'],
            'lm_license_code_custom_format' => ['required_if:lm_license_code_format,custom', 'string', 'max:255'],
            'lm_license_code_random_length' => ['required_if:lm_license_code_format,random', 'integer', 'min:20', 'max:100'],
            'lm_license_code_case_insensitive' => ['required', 'in:uppercase,lowercase,mix'],
            'lm_license_encryption_cipher' => ['required', 'string', 'in:aes-256-cbc,aes-128-cbc,aes-256-gcm,aes-128-gcm'],
            'lm_product_unique_id_format' => ['required', 'string', 'max:255', 'in:uuid,ulid,hex8,hex16'],
            'lm_product_version_unique_id_format' => ['required', 'string', 'max:255', 'in:uuid,ulid,hex8,hex16'],
            'lm_add_entries_for_failed_activation_attempts' => [new OnOffRule()],
            'lm_add_entries_for_failed_update_download_attempts' => [new OnOffRule()],
            'lm_deactivate_old_activations_on_new_activation' => [new OnOffRule()],
            'lm_add_domain_of_first_activation_as_licensed_domain' => [new OnOffRule()],
            'lm_send_expiration_warnings' => [new OnOffRule()],
            'lm_expiration_warning_days' => ['nullable', 'string', 'max:255'],
            'lm_enable_public_verification' => [new OnOffRule()],
            'lm_customer_page_licenses' => [new OnOffRule()],
            'lm_customer_page_activations' => [new OnOffRule()],
            'lm_allow_customer_deactivation' => [new OnOffRule()],
            'lm_get_more_licenses_url' => ['nullable', 'url', 'max:255'],
            'lm_enable_webhooks' => [new OnOffRule()],
            'lm_webhook_url' => ['nullable', 'url', 'max:255'],
            'lm_webhook_secret' => ['nullable', 'string', 'max:255'],
            'lm_enable_envato_integration' => [new OnOffRule()],
            'lm_envato_personal_token' => ['nullable', 'string', 'max:255'],
            'lm_envato_owner_username' => ['nullable', 'string', 'max:255'],
            'lm_envato_marketplace' => ['nullable', 'string', Rule::enum(EnvatoSite::class)],
            'lm_default_envato_license_uses_limit' => ['nullable', 'numeric', 'min:0'],
            'lm_default_envato_parallel_uses_limit' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
