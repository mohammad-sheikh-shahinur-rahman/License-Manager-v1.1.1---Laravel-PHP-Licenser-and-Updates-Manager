<?php

namespace Botble\LicenseManager\Http\Middleware;

use Botble\LicenseManager\Http\Middleware\Concerns\InvalidHeaderResponse;
use Botble\LicenseManager\LicenseManager;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageCheck
{
    use InvalidHeaderResponse;

    public function __construct(protected Application $app, protected LicenseManager $licenseManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-API-LANGUAGE') ?: $request->header('LB-LANG', 'en');

        $this->licenseManager->setClientLocale($locale);

        $this->app->setLocale($locale);

        return $next($request);
    }
}
