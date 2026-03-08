<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Models\ActivityLog;
use Illuminate\Http\JsonResponse;

class BlockedProductLicenseController extends BaseController
{
    public function store(string $productLicenseId): JsonResponse
    {
        $productLicense = ProductLicenseController::findLicense($productLicenseId);

        if (! $productLicense) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.not_found'),
            ]);
        }

        $productLicense->update([
            'is_valid' => false,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_blocked_via_api', ['license' => e($productLicense->license_code)]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.blocked'),
        ]);
    }

    public function destroy(string $productLicenseId): JsonResponse
    {
        $productLicense = ProductLicenseController::findLicense($productLicenseId);

        if (! $productLicense) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.not_found'),
            ]);
        }

        $productLicense->update([
            'is_valid' => true,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_unblocked_via_api', ['license' => e($productLicense->license_code)]));

        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.unblocked'),
        ]);
    }
}
