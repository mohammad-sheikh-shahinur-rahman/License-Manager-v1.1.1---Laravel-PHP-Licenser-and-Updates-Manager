<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithProducts;
use Botble\LicenseManager\Http\Requests\Api\UpdateCheckRequest;
use Illuminate\Http\JsonResponse;

class UpdateCheckController
{
    use InteractsWithProducts;

    public function __invoke(UpdateCheckRequest $request): JsonResponse
    {
        $product = $this->findProductByUniqueId($request->get('product_id'));

        if (! $product) {
            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.product_not_found'),
                'update_available' => false,
                'version' => null,
            ], 200);
        }

        $latestVersion = $this->findLatestVersionByProduct($product);

        if (! $latestVersion) {
            return new JsonResponse([
                'status' => true,
                'is_active' => true,
                'message' => trans('plugins/license-manager::license-manager.api.external.latest_version_in_use'),
                'update_available' => false,
                'version' => null,
            ], 200);
        }

        $currentVersion = $request->input('current_version');

        if (version_compare($currentVersion, $latestVersion->version, '>=')) {
            return new JsonResponse([
                'status' => true,
                'is_active' => true,
                'message' => trans('plugins/license-manager::license-manager.api.external.latest_version_in_use'),
                'update_available' => false,
                'version' => null,
            ], 200);
        }

        $versionData = $this->prepareProductVersionData($latestVersion, $product);

        $responseData = [
            'status' => true,
            'is_active' => true,
            'message' => trans('plugins/license-manager::license-manager.api.external.update_available'),
            'update_available' => true,
            'version' => $versionData['version'] ?? null,
            'release_date' => $versionData['released_at'] ?? null,
            'summary' => $versionData['summary'] ?? null,
            'changelog' => $versionData['changelog'] ?? null,
            'update_id' => $versionData['update_id'] ?? null,
            'has_sql' => $versionData['has_sql'] ?? false,
        ];

        $responseData = apply_filters('lm_api_update_check_response', $responseData, $latestVersion, $product);

        return new JsonResponse($responseData, 200);
    }
}
