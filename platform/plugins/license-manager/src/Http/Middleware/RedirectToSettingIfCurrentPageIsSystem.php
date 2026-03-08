<?php

namespace Botble\LicenseManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToSettingIfCurrentPageIsSystem
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('system.index')) {
            return redirect()->route('settings.index');
        }

        return $next($request);
    }
}
