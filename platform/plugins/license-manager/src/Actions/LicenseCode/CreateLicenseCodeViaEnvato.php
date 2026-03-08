<?php

namespace Botble\LicenseManager\Actions\LicenseCode;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class CreateLicenseCodeViaEnvato
{
    public function handle(Product $product, string $purchaseCode, array $envatoResponse): ProductLicense
    {
        /** @var \Botble\LicenseManager\Models\ProductLicense $license */
        $license = ProductLicense::query()->create([
            'product_reference_id' => $product->reference_id,
            'license_code' => $purchaseCode,
            'type' => Arr::get($envatoResponse, 'license'),
            'is_envato' => true,
            'customer_id' => Arr::get($envatoResponse, 'buyer'),
            'support_until' => Date::parse(Arr::get($envatoResponse, 'supported_until')),
            'uses' => (int) setting('lm_default_envato_license_uses_limit', 0),
            'parallel_uses' => setting('lm_default_envato_parallel_uses_limit') ?: null,
            'is_valid' => true,
        ]);

        return $license;
    }
}
