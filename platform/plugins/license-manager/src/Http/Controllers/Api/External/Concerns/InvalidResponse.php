<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External\Concerns;

use Illuminate\Http\JsonResponse;

trait InvalidResponse
{
    protected function responseInvalidLicense(): JsonResponse
    {
        return response()->json([
            'message' => trans('plugins/license-manager::license-manager.api.external.invalid_license_short'),
        ], 400);
    }
}
