<?php

namespace Botble\LicenseManager\Http\Middleware;

use Botble\LicenseManager\Enums\ApiKeyType;

class InternalCheck extends ApiKeyCheck
{
    protected function apiKeyType(): ApiKeyType
    {
        return ApiKeyType::Internal;
    }
}
