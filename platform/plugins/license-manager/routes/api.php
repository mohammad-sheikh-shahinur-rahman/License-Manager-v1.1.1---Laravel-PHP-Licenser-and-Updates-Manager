<?php

use Botble\LicenseManager\Http\Middleware\ExternalCheck;
use Botble\LicenseManager\Http\Middleware\ForceJsonResponse;
use Botble\LicenseManager\Http\Middleware\InternalCheck;
use Botble\LicenseManager\Http\Middleware\IpAddressCheck;
use Botble\LicenseManager\Http\Middleware\LanguageCheck;
use Botble\LicenseManager\Http\Middleware\UrlCheck;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::middleware([ForceJsonResponse::class, ThrottleRequests::using('license-manager')])
    ->prefix('api')
    ->namespace('Botble\LicenseManager\Http\Controllers\Api')
    ->group(function (): void {
        Route::middleware([
            LanguageCheck::class,
            IpAddressCheck::class,
            UrlCheck::class,
        ])
            ->group(function (): void {
                Route::prefix('internal')
                    ->middleware(InternalCheck::class)
                    ->namespace('Internal')
                    ->group(function (): void {
                        Route::get('connection-check', [
                            'uses' => 'ConnectionCheckController@__invoke',
                        ]);

                        Route::get('products', 'ProductController@index');
                        Route::get('products/{product}', 'ProductController@show');
                        Route::post('products', 'ProductController@store');
                        Route::put('products/{product}', 'ProductController@update');

                        Route::post('activated-products/{product}', 'ActivatedProductController@store');
                        Route::delete('activated-products/{product}', 'ActivatedProductController@destroy');

                        Route::get('products/{product}/versions', 'ProductVersionController@index');
                        Route::post('products/{product}/versions', 'ProductVersionController@store');
                        Route::get('products/{product}/versions/{version}', 'ProductVersionController@show');
                        Route::post('products/{product}/versions/{version}', 'ProductVersionController@update');

                        Route::get('product-licenses', 'ProductLicenseController@index');
                        Route::post('product-licenses', 'ProductLicenseController@store');
                        Route::get('product-licenses/{productLicense}', 'ProductLicenseController@show');
                        Route::put('product-licenses/{productLicense}', 'ProductLicenseController@update');

                        Route::post('blocked-product-licenses/{productLicense}', 'BlockedProductLicenseController@store');
                        Route::delete('blocked-product-licenses/{productLicense}', 'BlockedProductLicenseController@destroy');

                        Route::post('activated-product-activations/{productActivation}', 'ActivatedProductActivationController@store');
                        Route::delete('activated-product-activations/{productActivation}', 'ActivatedProductActivationController@destroy');

                        Route::get('customers', 'CustomerController@index');
                        Route::post('customers', 'CustomerController@store');
                        Route::get('customers/{customer}', 'CustomerController@show');
                        Route::put('customers/{customer}', 'CustomerController@update');
                    });

                Route::prefix('external')
                    ->middleware(ExternalCheck::class)
                    ->namespace('External')
                    ->group(function (): void {
                        Route::get('connection-check', [
                            'uses' => 'ConnectionCheckController@__invoke',
                        ]);

                        Route::post('license/activate', [
                            'uses' => 'LicenseActivateController@__invoke',
                        ]);

                        Route::post('license/verify', [
                            'uses' => 'LicenseVerifyController@__invoke',
                        ]);

                        Route::post('license/deactivate', [
                            'uses' => 'LicenseDeactivateController@__invoke',
                        ]);

                        Route::post('update/latest', [
                            'uses' => 'UpdateLatestController@__invoke',
                        ]);

                        Route::post('update/check', [
                            'uses' => 'UpdateCheckController@__invoke',
                        ]);

                        Route::get('update/{version}/download/{type}/size', [
                            'uses' => 'UpdateDownloadSizeController@__invoke',
                        ]);

                        Route::post('update/{version}/download/{type}', [
                            'uses' => 'UpdateDownloadController@__invoke',
                        ]);

                        Route::post('license/check', [
                            'uses' => 'LicenseCheckController@__invoke',
                        ]);
                    });
            });

        Route::get('health-check', [
            'uses' => 'HealthCheckController@__invoke',
        ]);

    });
