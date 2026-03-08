<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Illuminate\Http\JsonResponse;

class ActivatedProductController extends BaseController
{
    public function store(string $productId): JsonResponse
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        $product->update([
            'is_active' => true,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_published_via_api', ['product' => e($product->name)]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.products.activated'),
        ]);
    }

    public function destroy(string $productId): JsonResponse
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        $product->update([
            'is_active' => false,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_unpublished_via_api', ['product' => e($product->name)]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.products.deactivated'),
        ]);
    }
}
