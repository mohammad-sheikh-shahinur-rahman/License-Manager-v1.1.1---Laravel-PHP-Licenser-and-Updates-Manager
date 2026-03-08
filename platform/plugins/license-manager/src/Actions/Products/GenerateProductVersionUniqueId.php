<?php

namespace Botble\LicenseManager\Actions\Products;

class GenerateProductVersionUniqueId
{
    public function handle(): string
    {
        return (new GenerateUniqueId())
            ->handle(setting('lm_product_unique_id_format', 'hex8'));
    }
}
