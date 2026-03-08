<?php

use Botble\Base\Facades\AdminHelper;
use Botble\LicenseManager\Http\Controllers\PublicLicenseController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    require __DIR__ . '/web-admin.php';
});

Route::group(
    [
        'middleware' => ['web', 'core'],
        'as' => 'lm.customer.',
    ],
    function (): void {
        require __DIR__ . '/web-customer.php';
    }
);

Route::redirect('/login', '/customer/login')
    ->name('login');

Route::middleware(['web', 'core'])->get('/customer/login/via-envato/callback', fn () => redirect()->to(
    route('auth.social.callback', ['provider' => 'envato']) . '?' . http_build_query(request()->query())
))->name('lm.customer.auth.envato.legacy-callback');

Route::group(
    [
        'middleware' => ['web', 'throttle:60,1'],
        'as' => 'lm.public.',
    ],
    function (): void {
        Route::get('/verify', [PublicLicenseController::class, 'showVerifyForm'])
            ->name('verify.form')
            ->middleware('lm.public.verify.enabled');

        Route::post('/verify', [PublicLicenseController::class, 'verify'])
            ->name('verify')
            ->middleware('lm.public.verify.enabled');
    }
);
