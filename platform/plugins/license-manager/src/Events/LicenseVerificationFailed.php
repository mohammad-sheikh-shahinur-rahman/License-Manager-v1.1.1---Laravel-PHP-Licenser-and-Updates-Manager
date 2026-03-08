<?php

namespace Botble\LicenseManager\Events;

use Botble\LicenseManager\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class LicenseVerificationFailed
{
    use Dispatchable;

    public function __construct(
        public Product $product,
        public Request $request,
    ) {
    }
}
