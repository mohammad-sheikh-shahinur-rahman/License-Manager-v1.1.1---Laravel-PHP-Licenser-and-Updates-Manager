<?php

namespace Botble\LicenseManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicVerifyEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(setting('lm_enable_public_verification') != '1', 404);

        return $next($request);
    }
}
