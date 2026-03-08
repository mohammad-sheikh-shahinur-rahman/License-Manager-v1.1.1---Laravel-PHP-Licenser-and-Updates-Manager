<?php

namespace Botble\LicenseManager\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthCheckController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.health_check.success'),
        ]);
    }
}
