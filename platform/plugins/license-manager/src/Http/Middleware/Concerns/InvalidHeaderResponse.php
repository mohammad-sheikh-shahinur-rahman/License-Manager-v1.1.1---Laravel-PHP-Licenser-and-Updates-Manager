<?php

namespace Botble\LicenseManager\Http\Middleware\Concerns;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait InvalidHeaderResponse
{
    protected function invalidHeaderResponse(string $key): Response
    {
        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.validation.missing_header_attribute', ['header' => $key]),
        ], Response::HTTP_BAD_REQUEST);
    }
}
