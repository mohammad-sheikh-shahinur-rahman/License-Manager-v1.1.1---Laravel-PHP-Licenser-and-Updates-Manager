<?php

namespace Botble\LicenseManager\Http\Middleware;

use Botble\LicenseManager\Http\Middleware\Concerns\InvalidHeaderResponse;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Rules\BlacklistedDomainRule;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UrlCheck
{
    use InvalidHeaderResponse;

    public function __construct(protected LicenseManager $licenseManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Support both X-API-URL (new) and LB-URL (legacy)
        $url = $request->header('X-API-URL') ?: $request->header('LB-URL');

        if (! $url) {
            return $this->invalidHeaderResponse('X-API-URL');
        }

        try {
            Validator::make(['url' => $url], [
                'url' => ['url', new BlacklistedDomainRule()],
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse([
                'is_active' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        $this->licenseManager->setClientUrl($url);

        return $next($request);
    }
}
