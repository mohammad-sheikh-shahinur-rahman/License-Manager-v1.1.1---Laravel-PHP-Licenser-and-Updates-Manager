<?php

namespace Botble\LicenseManager\Events;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Foundation\Events\Dispatchable;

class UpdateDownloaded
{
    use Dispatchable;

    public function __construct(
        public ProductVersion $productVersion,
        public Product $product,
        public string $type,
    ) {
    }
}
