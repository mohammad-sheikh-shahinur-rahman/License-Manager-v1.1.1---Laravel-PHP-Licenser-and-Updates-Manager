<?php

namespace Botble\LicenseManager\Actions\Products;

use Illuminate\Support\Str;

class GenerateUniqueId
{
    public function handle(string $format): string
    {
        return strtoupper(match ($format) {
            'ulid' => (string) Str::ulid(),
            'hex8' => substr(Str::ulid()->toHex(), -8),
            'hex16' => substr(Str::ulid()->toHex(), -16),
            default => (string) Str::orderedUuid(),
        });
    }
}
