<?php

use Botble\LegacyApi\Http\Controllers\LegacyConnectionController;
use Botble\LegacyApi\Http\Controllers\LegacyLicenseController;
use Botble\LegacyApi\Http\Controllers\LegacyProductController;
use Botble\LegacyApi\Http\Controllers\LegacyVersionController;
use Botble\LegacyApi\Http\Middleware\LegacyInternalCheck;
use Botble\LicenseManager\Http\Controllers\Api\External\ConnectionCheckController;
use Botble\LicenseManager\Http\Controllers\Api\External\LicenseActivateController;
use Botble\LicenseManager\Http\Controllers\Api\External\LicenseDeactivateController;
use Botble\LicenseManager\Http\Controllers\Api\External\LicenseVerifyController;
use Botble\LicenseManager\Http\Controllers\Api\External\UpdateCheckController;
use Botble\LicenseManager\Http\Controllers\Api\External\UpdateDownloadController;
use Botble\LicenseManager\Http\Controllers\Api\External\UpdateDownloadSizeController;
use Botble\LicenseManager\Http\Controllers\Api\External\UpdateLatestController;
use Botble\LicenseManager\Http\Middleware\ExternalCheck;
use Botble\LicenseManager\Http\Middleware\ForceJsonResponse;
use Botble\LicenseManager\Http\Middleware\IpAddressCheck;
use Botble\LicenseManager\Http\Middleware\LanguageCheck;
use Botble\LicenseManager\Http\Middleware\UrlCheck;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy External API Routes (CMS Core.php backward compatibility)
|--------------------------------------------------------------------------
|
| These routes replicate the original LicenseBox /api/ endpoints that
| CMS Core.php calls for license activation, verification, updates, etc.
| They delegate to license-manager's existing External controllers.
|
*/
Route::middleware([
    ForceJsonResponse::class,
    ThrottleRequests::using('license-manager'),
    LanguageCheck::class,
    IpAddressCheck::class,
    UrlCheck::class,
    ExternalCheck::class,
])
    ->prefix('api')
    ->group(function (): void {
        Route::post('check_connection_ext', ConnectionCheckController::class);

        Route::post('activate_license', LicenseActivateController::class);
        Route::post('verify_license', LicenseVerifyController::class);
        Route::post('deactivate_license', LicenseDeactivateController::class);

        Route::post('check_update', UpdateCheckController::class);
        Route::post('latest_version', UpdateLatestController::class);

        Route::match(['get', 'post', 'head'], 'get_update_size', [UpdateDownloadSizeController::class, 'legacy']);
        Route::match(['get', 'head'], 'get_update_size/{version}', [UpdateDownloadSizeController::class, 'legacySingleParam']);
        Route::match(['get', 'post', 'head'], 'get_update_size/{type}/{version}', [UpdateDownloadSizeController::class, 'legacyPath']);

        Route::post('download_update', [UpdateDownloadController::class, 'legacy']);
        Route::post('download_update/{version}', [UpdateDownloadController::class, 'legacySingleParam']);
        Route::post('download_update/{type}/{version}', [UpdateDownloadController::class, 'legacyPath']);
    });

/*
|--------------------------------------------------------------------------
| Legacy Internal API Routes (LicenseBox admin/CI backward compatibility)
|--------------------------------------------------------------------------
|
| These routes replicate the original LicenseBox /api/ internal endpoints
| for product management, license CRUD, and version publishing.
| Registered at both /api/ and /api_internal/ prefixes for max compat.
|
*/
$legacyInternalMiddleware = [
    ForceJsonResponse::class,
    ThrottleRequests::using('license-manager'),
    LanguageCheck::class,
    IpAddressCheck::class,
    UrlCheck::class,
    LegacyInternalCheck::class,
];

$legacyInternalRoutes = function (): void {
    // Connection check
    Route::post('check_connection_int', LegacyConnectionController::class);

    // Products
    Route::post('add_product', [LegacyProductController::class, 'addProduct']);
    Route::post('get_product', [LegacyProductController::class, 'getProduct']);
    Route::post('get_products', [LegacyProductController::class, 'getProducts']);
    Route::post('mark_product_active', [LegacyProductController::class, 'markActive']);
    Route::post('mark_product_inactive', [LegacyProductController::class, 'markInactive']);

    // Versions (delegates to license-manager's existing LegacyVersionController)
    Route::post('edit_or_create_version', LegacyVersionController::class);

    // Licenses
    Route::post('create_license', [LegacyLicenseController::class, 'createLicense']);
    Route::post('edit_license', [LegacyLicenseController::class, 'editLicense']);
    Route::post('get_license', [LegacyLicenseController::class, 'getLicense']);
    Route::post('search_license', [LegacyLicenseController::class, 'searchLicense']);
    Route::post('delete_license', [LegacyLicenseController::class, 'deleteLicense']);
    Route::post('block_license', [LegacyLicenseController::class, 'blockLicense']);
    Route::post('unblock_license', [LegacyLicenseController::class, 'unblockLicense']);
    Route::post('deactivate_license_activations', [LegacyLicenseController::class, 'deactivateActivations']);
};

// Register at /api/ prefix (original LicenseBox URL)
Route::middleware($legacyInternalMiddleware)
    ->prefix('api')
    ->group($legacyInternalRoutes);

// Register at /api_internal/ prefix (existing legacy convention)
Route::middleware($legacyInternalMiddleware)
    ->prefix('api_internal')
    ->group($legacyInternalRoutes);
