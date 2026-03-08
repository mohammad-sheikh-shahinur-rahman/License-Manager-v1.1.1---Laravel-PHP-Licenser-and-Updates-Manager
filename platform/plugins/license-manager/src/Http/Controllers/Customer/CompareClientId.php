<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Illuminate\Support\Str;

trait CompareClientId
{
    protected function compareClient(string $clientId, string $targetClientId): bool
    {
        return Str::lower($clientId) === Str::lower($targetClientId);
    }
}
