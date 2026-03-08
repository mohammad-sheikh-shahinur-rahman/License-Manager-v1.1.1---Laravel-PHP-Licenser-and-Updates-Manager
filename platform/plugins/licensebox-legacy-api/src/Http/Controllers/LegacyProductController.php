<?php

namespace Botble\LegacyApi\Http\Controllers;

use Botble\LegacyApi\Http\Controllers\Concerns\LegacyResponse;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyProductController
{
    use LegacyResponse;

    public function addProduct(Request $request): JsonResponse
    {
        $productName = $request->input('product_name');

        if (empty($productName)) {
            return $this->missingValuesResponse();
        }

        $productId = $request->input('product_id');

        if (empty($productId)) {
            $productId = Str::upper(substr(md5(microtime()), 0, 8));
        }

        if (! preg_match('/^[a-z0-9]+$/i', $productId)) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_invalid'), 400);
        }

        if (Product::query()->where('reference_id', $productId)->exists()) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_exists'));
        }

        $product = Product::query()->create([
            'reference_id' => $productId,
            'envato_id' => $request->input('envato_item_id'),
            'name' => $productName,
            'description' => $request->input('product_details'),
            'license_update' => 0,
            'is_active' => 1,
        ]);

        if ($product) {
            ActivityLog::query()->create([
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.product_added', ['name' => e($productName)]),
                'created_at' => Carbon::now(),
            ]);

            return $this->legacySuccess(
                trans('plugins/licensebox-legacy-api::legacy-api.success.product_added', ['name' => $productName, 'id' => $productId]),
                ['product_id' => $productId]
            );
        }

        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_not_added'));
    }

    public function getProduct(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');

        if (empty($productId)) {
            return $this->missingValuesResponse();
        }

        $product = $this->findProductByLegacyId($productId);

        if (! $product) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_incorrect'));
        }

        $latestVersion = ProductVersion::query()
            ->where('product_reference_id', $product->reference_id)
            ->where('is_active', 1)
            ->latest('released_at')
            ->first();

        return new JsonResponse([
            'status' => true,
            'product_id' => $product->reference_id,
            'envato_item_id' => $product->envato_id,
            'product_name' => $product->name,
            'product_details' => $product->description,
            'latest_version' => $latestVersion?->version,
            'latest_version_release_date' => $latestVersion?->released_at,
            'is_product_active' => (bool) $product->is_active,
            'requires_license_for_downloading_updates' => (bool) $product->license_update,
        ]);
    }

    public function getProducts(): JsonResponse
    {
        $products = Product::query()->get();
        $productIds = $products->pluck('reference_id')->all();

        // Batch-load latest versions to avoid N+1 queries
        $latestVersions = ProductVersion::query()
            ->whereIn('product_reference_id', $productIds)
            ->where('is_active', 1)
            ->latest('released_at')
            ->get()
            ->groupBy('product_reference_id')
            ->map(fn ($versions) => $versions->first());

        $finalData = [];

        foreach ($products as $product) {
            $latestVersion = $latestVersions->get($product->reference_id);

            $finalData[] = [
                'product_id' => $product->reference_id,
                'envato_item_id' => $product->envato_id,
                'product_name' => $product->name,
                'product_details' => $product->description,
                'latest_version' => $latestVersion?->version,
                'latest_version_release_date' => $latestVersion?->released_at,
                'is_product_active' => (bool) $product->is_active,
                'requires_license_for_downloading_updates' => (bool) $product->license_update,
            ];
        }

        return new JsonResponse([
            'status' => true,
            'products' => $finalData,
        ]);
    }

    public function markActive(Request $request): JsonResponse
    {
        return $this->toggleProductStatus($request, 1, 'active');
    }

    public function markInactive(Request $request): JsonResponse
    {
        return $this->toggleProductStatus($request, 0, 'inactive');
    }

    protected function toggleProductStatus(Request $request, int $status, string $label): JsonResponse
    {
        $productId = $request->input('product_id');

        if (empty($productId)) {
            return $this->missingValuesResponse();
        }

        $product = $this->findProductByLegacyId($productId);

        if (! $product) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_incorrect'));
        }

        if ($product->is_active === $status) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_already_status', ['name' => $product->name, 'status' => $label]));
        }

        $product->update(['is_active' => $status]);

        ActivityLog::query()->create([
            'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.product_status_changed', ['name' => e($product->name), 'status' => $label]),
            'created_at' => Carbon::now(),
        ]);

        return $this->legacySuccess(trans('plugins/licensebox-legacy-api::legacy-api.success.product_status_changed', ['name' => $product->name, 'status' => $label]));
    }
}
