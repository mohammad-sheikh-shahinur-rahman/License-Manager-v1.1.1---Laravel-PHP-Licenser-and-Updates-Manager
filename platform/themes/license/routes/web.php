<?php

use Botble\Base\Forms\FormBuilder;
use Botble\LicenseManager\Http\Controllers\Customer\Auth\AuthenticatedSessionController;
use Botble\LicenseManager\Http\Controllers\Customer\DashboardController;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'core'])->group(function (): void {
    Route::get('/', function () {
        if (auth('lm_customer')->check()) {
            return app(DashboardController::class)->__invoke();
        }

        return app(AuthenticatedSessionController::class)->index(app(FormBuilder::class));
    })->name('public.index');

    Route::get('{slug}', [PublicController::class, 'getView'])
        ->where('slug', '[a-z0-9\-]+')
        ->middleware('auth:lm_customer')
        ->name('public.single');
});
