<?php

namespace Botble\LicenseManager\Actions\Products;

use Illuminate\Support\Str;

class GenerateProductUniqueId
{
    public function handle(): string
    {
        return strtoupper(match (setting('lm_product_unique_id_format', 'hex8')) {
            'ulid' => (string) Str::ulid(),
            'hex8' => substr(Str::ulid()->toHex(), -8),
            'hex16' => substr(Str::ulid()->toHex(), -16),
            default => (string) Str::orderedUuid(),
        });
    }
}
