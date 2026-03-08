<?php

namespace Botble\LicenseManager\Rules;

use Botble\LicenseManager\Rules\Concerns\DetermineContainsString;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class BlacklistedLicenseCodeRule implements ValidationRule
{
    use DetermineContainsString;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $blacklistedLicenseCodes = setting('lm_blacklisted_license_codes', '[]');
        $blacklistedLicenseCodes = $blacklistedLicenseCodes ? Arr::flatten(json_decode($blacklistedLicenseCodes, true)) : [];

        if (empty($blacklistedLicenseCodes)) {
            return;
        }

        if ($this->determineIfStringInList($value, $blacklistedLicenseCodes)) {
            $fail(trans('plugins/license-manager::license-manager.validation.field_blacklisted'));
        }
    }
}
