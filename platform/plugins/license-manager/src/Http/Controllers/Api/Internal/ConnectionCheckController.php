<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Illuminate\Http\JsonResponse;

class ConnectionCheckController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.internal.connection_check.success'),
        ]);
    }
}
