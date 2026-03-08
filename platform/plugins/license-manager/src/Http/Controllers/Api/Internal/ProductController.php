<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Actions\Products\GenerateProductUniqueId;
use Botble\LicenseManager\Http\Requests\ProductRequest;
use Botble\LicenseManager\Http\Resources\ProductJsonResource;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class ProductController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('is_active', true)
            ->paginate();

        return ProductJsonResource::collection($products);
    }

    public function show(string $productId): JsonResponse|ProductJsonResource
    {
        $product = Product::query()->find($productId)
            ?? Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        return ProductJsonResource::make($product);
    }

    public function store(ProductRequest $request, GenerateProductUniqueId $generateProductUniqueId): ProductJsonResource
    {
        $product = Product::query()->create(
            [
                ...$request->validated(),
                'unique_id' => $request->input('unique_id', $generateProductUniqueId->handle()),
            ],
        );

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_created_via_api', ['product' => e($product->name)]));

        return ProductJsonResource::make($product);
    }

    public function update(ProductRequest $request, string $productId): JsonResponse|ProductJsonResource
    {
        $product = Product::query()->find($productId)
            ?? Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        $product->update(Arr::except($request->validated(), 'unique_id'));

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_updated_via_api', ['product' => e($product->name)]));

        return ProductJsonResource::make($product);
    }
}
