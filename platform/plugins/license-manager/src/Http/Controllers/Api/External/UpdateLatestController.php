<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithProducts;
use Botble\LicenseManager\Http\Requests\Api\UpdateLatestRequest;
use Illuminate\Http\JsonResponse;

class UpdateLatestController
{
    use InteractsWithProducts;

    public function __invoke(UpdateLatestRequest $request): JsonResponse
    {
        $product = $this->findProductByUniqueId($request->get('product_id'));

        if (! $product) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.external.product_not_found'),
            ], 404);
        }

        $latestVersion = $this->findLatestVersionByProduct($product);

        if (! $latestVersion) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.external.latest_version_not_found'),
            ], 404);
        }

        $responseData = [
            'data' => $this->prepareProductVersionData($latestVersion, $product),
        ];

        $responseData = apply_filters('lm_api_update_latest_response', $responseData, $latestVersion, $product);

        return response()->json($responseData);
    }
}
