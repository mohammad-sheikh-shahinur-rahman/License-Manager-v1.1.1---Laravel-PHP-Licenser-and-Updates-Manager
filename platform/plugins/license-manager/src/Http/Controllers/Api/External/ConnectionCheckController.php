<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Illuminate\Http\JsonResponse;

class ConnectionCheckController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => true,
            'is_active' => true,
            'message' => trans('plugins/license-manager::license-manager.api.external.connection_check.success'),
        ], 200);
    }
}
