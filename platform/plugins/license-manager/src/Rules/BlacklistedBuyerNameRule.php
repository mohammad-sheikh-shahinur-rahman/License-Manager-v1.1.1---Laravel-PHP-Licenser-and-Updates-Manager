<?php

namespace Botble\LicenseManager\Rules;

use Botble\LicenseManager\Rules\Concerns\DetermineContainsString;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class BlacklistedBuyerNameRule implements ValidationRule
{
    use DetermineContainsString;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $blacklistedBuyerNames = setting('lm_blacklisted_buyer_names', '[]');
        $blacklistedBuyerNames = $blacklistedBuyerNames ? Arr::flatten(json_decode($blacklistedBuyerNames, true)) : [];

        if (empty($blacklistedBuyerNames)) {
            return;
        }

        if ($this->determineIfStringInList($value, $blacklistedBuyerNames)) {
            $fail(trans('plugins/license-manager::license-manager.validation.field_blacklisted'));
        }
    }
}
