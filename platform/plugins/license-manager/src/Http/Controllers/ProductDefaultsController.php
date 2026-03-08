<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductDefaultsController extends LicenseManagerController
{
    public function __invoke(string $referenceId): JsonResponse
    {
        $product = Product::query()
            ->where('reference_id', $referenceId)
            ->firstOrFail();

        return response()->json([
            'default_license_type' => $product->default_license_type,
            'default_uses' => $product->default_uses,
            'default_parallel_uses' => $product->default_parallel_uses,
            'default_expiry_days' => $product->default_expiry_days,
            'default_updates_until_days' => $product->default_updates_until_days,
            'default_support_until_days' => $product->default_support_until_days,
            'default_comments' => $product->default_comments,
        ]);
    }
}
