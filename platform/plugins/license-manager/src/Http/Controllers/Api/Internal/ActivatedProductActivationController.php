<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Illuminate\Http\JsonResponse;

class ActivatedProductActivationController extends BaseController
{
    public function store(string $productActivationId)
    {
        $productActivation = ProductActivation::query()->find($productActivationId);

        if (! $productActivation) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_activations.not_found'),
            ]);
        }

        $productActivation->update([
            'is_active' => true,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.activation_activated_via_api', [
            'license' => e($productActivation->license_code),
            'url' => e($productActivation->url),
        ]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.product_activations.activated'),
        ]);
    }

    public function destroy(string $productActivationId): JsonResponse
    {
        $productActivation = ProductActivation::query()->find($productActivationId);

        if (! $productActivation) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_activations.not_found'),
            ]);
        }

        $productActivation->update([
            'is_active' => false,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.activation_deactivated_via_api', [
            'license' => e($productActivation->license_code),
            'url' => e($productActivation->url),
        ]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.product_activations.deactivated'),
        ]);
    }
}
