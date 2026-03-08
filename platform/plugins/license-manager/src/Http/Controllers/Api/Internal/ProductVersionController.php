<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Actions\ProductVersions\StoreProductVersion;
use Botble\LicenseManager\Actions\ProductVersions\UpdateProductVersion;
use Botble\LicenseManager\Http\Requests\ProductVersionRequest;
use Botble\LicenseManager\Http\Resources\ProductVersionJsonResource;
use Botble\LicenseManager\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ProductVersionController extends BaseController
{
    public function index(string $productId): JsonResponse|AnonymousResourceCollection
    {
        /**
         * @var Product $product
         */
        $product = Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product.not_found'),
            ]);
        }

        $product->loadMissing('versions');

        return ProductVersionJsonResource::collection($product->versions);
    }

    public function show(string $productId, string $versionId): JsonResponse|ProductVersionJsonResource
    {
        /**
         * @var Product $product
         */
        $product = Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        $product->loadMissing('versions');

        $version = $product->versions->where('version_id', $versionId)->first();

        if (! $version) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_versions.not_found'),
            ]);
        }

        return ProductVersionJsonResource::make($version);
    }

    public function store(
        string $productId,
        ProductVersionRequest $request,
        StoreProductVersion $storeProductVersion
    ): JsonResponse|ProductVersionJsonResource {
        /**
         * @var Product $product
         */
        $product = Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        try {
            $productVersion = $storeProductVersion->handle($product, $request->validated());
        } catch (ValidationException $e) {
            return $this->response([
                'is_active' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return ProductVersionJsonResource::make($productVersion);
    }

    public function update(
        string $productId,
        ProductVersionRequest $request,
        string $versionId,
        UpdateProductVersion $updateProductVersion
    ): JsonResponse|ProductVersionJsonResource {
        /**
         * @var Product $product
         */
        $product = Product::query()->where('reference_id', $productId)->first();

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.products.not_found'),
            ]);
        }

        $productVersion = $product->versions->where('version_id', $versionId)->first();

        if (! $productVersion) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_versions.not_found'),
            ]);
        }

        $updateProductVersion->handle($product, $productVersion, $request->validated());

        return ProductVersionJsonResource::make($productVersion);
    }

    protected function response(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
