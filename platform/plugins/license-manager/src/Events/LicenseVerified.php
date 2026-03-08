<?php

namespace Botble\LicenseManager\Events;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Illuminate\Foundation\Events\Dispatchable;

class LicenseVerified
{
    use Dispatchable;

    public function __construct(
        public ProductActivation $activation,
        public Product $product,
    ) {
    }
}
