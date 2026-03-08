<?php

namespace Botble\LicenseManager\Rules\Concerns;

use Illuminate\Support\Str;

trait DetermineContainsString
{
    public function determineIfStringInList(string $string, array $list): bool
    {
        foreach ($list as $item) {
            if (
                Str::startsWith($item, '*')
                && Str::endsWith($item, '*')
                && Str::contains($string, Str::before(Str::after($item, '*'), '*'))
            ) {
                return true;
            }

            if (
                Str::startsWith($item, '*')
                && Str::endsWith($string, Str::after($item, '*'))
            ) {
                return true;
            }

            if (
                Str::endsWith($item, '*')
                && Str::startsWith($string, Str::before($item, '*'))
            ) {
                return true;
            }

            if (Str::lower($item) === Str::lower($string)) {
                return true;
            }
        }

        return false;
    }
}
