<?php

namespace Botble\LicenseManager\Http\Middleware;

use Botble\LicenseManager\Http\Middleware\Concerns\InvalidHeaderResponse;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Rules\BlacklistedIpRule;
use Botble\LicenseManager\Support\IpHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class IpAddressCheck
{
    use InvalidHeaderResponse;

    public function __construct(protected LicenseManager $licenseManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Support both X-API-IP (new) and LB-IP (legacy)
        $ip = $request->header('X-API-IP') ?: $request->header('LB-IP');

        if (! $ip) {
            return $this->invalidHeaderResponse('X-API-IP');
        }

        try {
            Validator::make(['ip_address' => $ip], [
                'ip_address' => ['ip', new BlacklistedIpRule()],
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse([
                'is_active' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        $this->licenseManager->setClientIp(IpHelper::normalize($ip));

        return $next($request);
    }
}
