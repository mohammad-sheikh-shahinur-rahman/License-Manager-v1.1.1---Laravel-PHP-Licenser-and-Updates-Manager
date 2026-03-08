<?php

use Botble\LicenseManager\Http\Controllers\Customer\ActivatedProductActivationController;
use Botble\LicenseManager\Http\Controllers\Customer\Auth\AuthenticatedSessionController;
use Botble\LicenseManager\Http\Controllers\Customer\Auth\NewPasswordController;
use Botble\LicenseManager\Http\Controllers\Customer\Auth\PasswordResetLinkController;
use Botble\LicenseManager\Http\Controllers\Customer\AvatarSettingController;
use Botble\LicenseManager\Http\Controllers\Customer\BasicSettingController;
use Botble\LicenseManager\Http\Controllers\Customer\DashboardController;
use Botble\LicenseManager\Http\Controllers\Customer\PasswordSettingController;
use Botble\LicenseManager\Http\Controllers\Customer\ProductActivationController;
use Botble\LicenseManager\Http\Controllers\Customer\ProductLicenseController;
use Botble\LicenseManager\Http\Controllers\Customer\SettingController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:lm_customer']], function (): void {
    Route::get('/dashboard', [DashboardController::class, '__invoke'])
        ->name('dashboard');

    Route::group(['prefix' => 'customer'], function (): void {
        Route::prefix('product-licenses')->group(function (): void {
            Route::match(['POST', 'GET'], '', [ProductLicenseController::class, 'index'])
                ->name('product-licenses.index');
        });

        Route::prefix('product-activations')->group(function (): void {
            Route::match(['POST', 'GET'], '', [ProductActivationController::class, 'index'])
                ->name('product-activations.index');
        });

        Route::prefix('activated-product-activations/{productActivation}')
            ->name('activated-product-activations.')
            ->group(function (): void {
                Route::delete('', [
                    'as' => 'destroy',
                    'uses' => ActivatedProductActivationController::class . '@destroy',
                ]);
            });

        Route::prefix('settings')->as('settings.')->group(function (): void {
            Route::get('', [SettingController::class, 'index'])
                ->name('index');

            Route::post('', [BasicSettingController::class, '__invoke'])
                ->name('basic');

            Route::post('avatar', [AvatarSettingController::class, '__invoke'])
                ->name('avatar');

            Route::post('password', [PasswordSettingController::class, '__invoke'])
                ->name('password');
        });

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('auth.logout');
    });
});

Route::group(['prefix' => 'customer'], function (): void {
    Route::group(['middleware' => ['guest:lm_customer']], function (): void {
        Route::get('login', [AuthenticatedSessionController::class, 'index'])
            ->name('auth.login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('auth.login.store');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('auth.password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('auth.password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('auth.password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('auth.password.store');
    });
});
