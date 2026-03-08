<?php

namespace Botble\LicenseManager\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\LicenseManager\Enums\ApiExternalScope;
use Botble\LicenseManager\Enums\ApiInternalScope;
use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class ApiKeyRequest extends Request
{
    public function rules(): array
    {
        $apiKey = $this->route('apiKey');

        $rules = [
            'key' => ['required', 'string', 'unique:lm_api_keys', 'max: 100'],
            'type' => ['required', Rule::enum(ApiKeyType::class)],
            'external_scopes' => ['required_if:type,' . ApiKeyType::External->value, 'array'],
            'external_scopes.*' => ['string', Rule::enum(ApiExternalScope::class)],
            'internal_scopes' => ['required_if:type,' . ApiKeyType::Internal->value, 'array'],
            'internal_scopes.*' => ['string', Rule::enum(ApiInternalScope::class)],
            'special' => [new OnOffRule()],
            'revoked' => [new OnOffRule()],
            'has_expired' => [new OnOffRule()],
            'expires_at' => ['required_if:has_expired,1', 'date', 'after:today', 'before:' . Date::now()->addYear()->addDay()->toDateString()],
        ];

        if ($apiKey) {
            unset($rules['key']);
        }

        return $rules;
    }
}
