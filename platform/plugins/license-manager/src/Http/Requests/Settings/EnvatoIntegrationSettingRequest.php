<?php

namespace Botble\LicenseManager\Http\Requests\Settings;

use Botble\LicenseManager\Enums\EnvatoSite;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class EnvatoIntegrationSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'lm_envato_owner_username' => ['nullable', 'string', 'max:255'],
            'lm_envato_marketplace' => ['required', 'string', Rule::enum(EnvatoSite::class)],
            'lm_blacklisted_buyer_names' => ['nullable', 'string', 'max:12345'],
            'lm_blacklisted_license_codes' => ['nullable', 'string', 'max:12345'],
            'lm_envato_personal_token' => ['nullable', 'string', 'max:255'],
            'lm_default_envato_license_uses_limit' => ['nullable', 'numeric'],
            'lm_default_envato_parallel_uses_limit' => ['nullable', 'numeric'],
        ];
    }
}
