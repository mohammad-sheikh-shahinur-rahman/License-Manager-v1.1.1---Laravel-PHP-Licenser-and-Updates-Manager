<?php

namespace Botble\LicenseManager\Http\Middleware;

use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Http\Middleware\Concerns\HasApiKey;
use Botble\LicenseManager\Http\Middleware\Concerns\InvalidHeaderResponse;
use Botble\LicenseManager\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyCheck
{
    use HasApiKey;
    use InvalidHeaderResponse;

    public function __construct(protected LicenseManager $licenseManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Support both X-API-KEY (new) and LB-API-KEY (legacy)
        $key = (string) ($request->header('X-API-KEY') ?: $request->header('LB-API-KEY'));

        if (! $key) {
            return $this->invalidHeaderResponse('X-API-KEY');
        }

        $apiKey = $this->findApiKey($key, $this->apiKeyType());

        if (! $apiKey) {
            return $this->invalidApiKeyResponse();
        }

        if (! $this->checkScope($request, $apiKey, $this->apiKeyType())) {
            return $this->forbiddenScopeResponse();
        }

        $this->licenseManager->setClientApiKey($apiKey);

        return $next($request);
    }

    protected function apiKeyType(): ApiKeyType
    {
        return ApiKeyType::External;
    }
}
