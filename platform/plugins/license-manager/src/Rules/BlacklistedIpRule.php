<?php

namespace Botble\LicenseManager\Rules;

use Botble\LicenseManager\Rules\Concerns\DetermineContainsString;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class BlacklistedIpRule implements ValidationRule
{
    use DetermineContainsString;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $blacklistedIps = setting('lm_blacklisted_ips', '[]');
        $blacklistedIps = $blacklistedIps ? Arr::flatten(json_decode($blacklistedIps, true)) : [];

        if (empty($blacklistedIps)) {
            return;
        }

        if ($this->determineIfStringInList($value, $blacklistedIps)) {
            $fail(trans('plugins/license-manager::license-manager.validation.ip_blacklisted'));
        }
    }
}
