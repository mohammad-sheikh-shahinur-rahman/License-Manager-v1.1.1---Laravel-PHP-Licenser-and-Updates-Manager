<?php

namespace Botble\LicenseManager\Events;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Foundation\Events\Dispatchable;

class LicenseActivated
{
    use Dispatchable;

    public function __construct(
        public ProductActivation $activation,
        public Product $product,
        public ProductLicense $licenseCode,
    ) {
    }
}
