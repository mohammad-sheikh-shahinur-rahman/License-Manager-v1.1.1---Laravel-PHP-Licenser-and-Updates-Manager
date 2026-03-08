<?php

namespace Botble\LicenseManager\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class ApiSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'lm_blacklist_domain_after_failed_attempts' => ['nullable', 'integer', 'gt:0'],
            'lm_blacklist_ip_after_failed_attempts' => ['nullable', 'integer', 'gt:0'],
            'lm_requests_rate_limiting_method' => ['required', 'in:ip_address,api_key,api_key_ip_address'],
            'lm_requests_rate_limiting_period' => ['nullable', 'integer', 'gt:0'],
            'lm_normalize_domain_variants' => ['nullable', 'boolean'],
            'lm_verify_license_ip' => ['nullable', 'boolean'],
            'lm_blacklisted_ips' => ['nullable', 'string'],
            'lm_blacklisted_domains' => ['nullable', 'string'],
            'lm_blacklisted_buyer_names' => ['nullable', 'string'],
            'lm_blacklisted_license_codes' => ['nullable', 'string'],
        ];
    }
}
