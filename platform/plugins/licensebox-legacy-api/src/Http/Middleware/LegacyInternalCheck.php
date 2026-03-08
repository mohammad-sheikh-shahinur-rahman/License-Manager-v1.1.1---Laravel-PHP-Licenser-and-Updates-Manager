<?php

namespace Botble\LegacyApi\Http\Middleware;

use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Http\Middleware\Concerns\HasApiKey;
use Botble\LicenseManager\Http\Middleware\Concerns\InvalidHeaderResponse;
use Botble\LicenseManager\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy internal API check that accepts both internal and external API keys.
 * This is for backward compatibility with old LicenseBox api_internal endpoints.
 */
class LegacyInternalCheck
{
    use HasApiKey;
    use InvalidHeaderResponse;

    public function __construct(protected LicenseManager $licenseManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) ($request->header('X-API-KEY') ?: $request->header('LB-API-KEY'));

        if (! $key) {
            return $this->invalidHeaderResponse('X-API-KEY');
        }

        // Try internal key first, then fall back to external (for backward compatibility)
        $apiKey = $this->findApiKey($key, ApiKeyType::Internal)
            ?? $this->findApiKey($key, ApiKeyType::External);

        if (! $apiKey) {
            return $this->invalidApiKeyResponse();
        }

        // For legacy internal endpoints, special keys bypass scope check
        if (! $apiKey->special) {
            return $this->forbiddenScopeResponse();
        }

        $this->licenseManager->setClientApiKey($apiKey);

        return $next($request);
    }
}
