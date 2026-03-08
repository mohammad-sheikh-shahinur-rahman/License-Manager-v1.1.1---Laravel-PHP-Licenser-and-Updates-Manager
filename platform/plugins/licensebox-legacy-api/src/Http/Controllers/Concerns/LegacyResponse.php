<?php

namespace Botble\LegacyApi\Http\Controllers\Concerns;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Http\JsonResponse;

trait LegacyResponse
{
    protected function legacySuccess(string $message, array $data = []): JsonResponse
    {
        return new JsonResponse(array_merge([
            'status' => true,
            'message' => $message,
        ], $data));
    }

    protected function legacyError(string $message, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'message' => $message,
        ], $status);
    }

    protected function findProductByLegacyId(string $productId): ?Product
    {
        return Product::query()->where('reference_id', $productId)->first();
    }

    protected function findLicenseByCode(string $licenseCode): ?ProductLicense
    {
        return ProductLicense::query()->where('license_code', $licenseCode)->first();
    }

    protected function missingValuesResponse(): JsonResponse
    {
        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.missing_values'), 400);
    }
}
