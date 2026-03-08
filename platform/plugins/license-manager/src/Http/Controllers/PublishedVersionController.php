<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;

class PublishedVersionController extends LicenseManagerController
{
    public function store(Product $product, ProductVersion $version)
    {
        $version->update([
            'is_active' => true,
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.products.versions.index', [$product]))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Product $product, ProductVersion $version)
    {
        $version->update([
            'is_active' => false,
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.products.versions.index', [$product]))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
