<?php

namespace Botble\LicenseManager\Rules;

use Botble\LicenseManager\Rules\Concerns\DetermineContainsString;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class BlacklistedDomainRule implements ValidationRule
{
    use DetermineContainsString;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $blacklistedDomains = setting('lm_blacklisted_domains', '[]');
        $blacklistedDomains = $blacklistedDomains ? Arr::flatten(json_decode($blacklistedDomains, true)) : [];

        if (empty($blacklistedDomains)) {
            return;
        }

        $domain = parse_url($value, PHP_URL_HOST) ?: $value;

        if ($this->determineIfStringInList($domain, $blacklistedDomains)) {
            $fail(trans('plugins/license-manager::license-manager.validation.domain_blacklisted'));
        }
    }
}
