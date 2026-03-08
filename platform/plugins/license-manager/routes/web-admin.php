<?php

use Botble\LicenseManager\Http\Controllers\ActivatedProductActivationController;
use Botble\LicenseManager\Http\Controllers\ActivityLogController;
use Botble\LicenseManager\Http\Controllers\BlockedLicenseController;
use Botble\LicenseManager\Http\Controllers\BulkLicenseController;
use Botble\LicenseManager\Http\Controllers\CustomerController;
use Botble\LicenseManager\Http\Controllers\CustomerSearchController;
use Botble\LicenseManager\Http\Controllers\DashboardWidgetController;
use Botble\LicenseManager\Http\Controllers\ExportLicenseController;
use Botble\LicenseManager\Http\Controllers\GenerateHelperController;
use Botble\LicenseManager\Http\Controllers\ImportLicenseController;
use Botble\LicenseManager\Http\Controllers\ManualCronController;
use Botble\LicenseManager\Http\Controllers\PhpObfuscatorController;
use Botble\LicenseManager\Http\Controllers\ProductActivationController;
use Botble\LicenseManager\Http\Controllers\ProductController;
use Botble\LicenseManager\Http\Controllers\ProductDefaultsController;
use Botble\LicenseManager\Http\Controllers\ProductLicenseController;
use Botble\LicenseManager\Http\Controllers\ProductVersionController;
use Botble\LicenseManager\Http\Controllers\PublishedVersionController;
use Botble\LicenseManager\Http\Controllers\SampleAppDownloadController;
use Botble\LicenseManager\Http\Controllers\Settings\ApiKeyController;
use Botble\LicenseManager\Http\Controllers\Settings\ApiSettingController;
use Botble\LicenseManager\Http\Controllers\Settings\GeneralSettingController;
use Botble\LicenseManager\Http\Controllers\Settings\LegacyMigrationController;
use Botble\LicenseManager\Http\Controllers\UpdateDownloadController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'license-manager'], function (): void {

    Route::group(['prefix' => 'customers', 'as' => 'lm.customers.'], function (): void {
        Route::resource('', CustomerController::class)->parameters(['' => 'customer']);
        Route::get('search', [
            'uses' => CustomerSearchController::class . '@__invoke',
            'permission' => false,
        ])->name('search');
    });

    Route::group(['prefix' => 'activations', 'as' => 'lm.activations.'], function (): void {
        Route::match(['POST', 'GET'], '', [ProductActivationController::class, 'index'])->name('index');
        Route::delete('{productActivation}', [ProductActivationController::class, 'destroy'])->name('destroy');

        Route::prefix('activated-product-activations/{productActivation}')
            ->name('activated-product-activations.')
            ->group(function (): void {
                Route::post('', [
                    'as' => 'store',
                    'uses' => ActivatedProductActivationController::class . '@store',
                    'permission' => 'lm.activations.edit',
                ]);

                Route::delete('', [
                    'as' => 'destroy',
                    'uses' => ActivatedProductActivationController::class . '@destroy',
                    'permission' => 'lm.activations.edit',
                ]);
            });
    });

    Route::group(['prefix' => 'bulk-generate', 'as' => 'lm.bulk-generate.'], function (): void {
        Route::get('/', [BulkLicenseController::class, 'index'])->name('index');
        Route::post('/', [BulkLicenseController::class, 'store'])->name('store');
        Route::get('success', [BulkLicenseController::class, 'success'])->name('success');
    });

    Route::group(['prefix' => 'licenses', 'as' => 'lm.licenses.'], function (): void {
        Route::resource('', ProductLicenseController::class)->parameters(['' => 'license']);

        Route::get('edit/{license}', [ProductLicenseController::class, 'edit'])->name('edit');

        Route::post('edit/{license}', [ProductLicenseController::class, 'update'])->name('edit.update');

        Route::delete('{license}', [ProductLicenseController::class, 'destroy'])->name('destroy');

        Route::post('{license}/send-email', [
            'as' => 'send-email',
            'uses' => ProductLicenseController::class . '@sendEmail',
            'permission' => 'lm.licenses.edit',
        ]);

        Route::prefix('blocked-licenses/{license}')->name('blocked-licenses.')->group(function (): void {
            Route::post('', [
                'as' => 'store',
                'uses' => BlockedLicenseController::class . '@store',
                'permission' => 'lm.licenses.edit',
            ]);

            Route::delete('', [
                'as' => 'destroy',
                'uses' => BlockedLicenseController::class . '@destroy',
                'permission' => 'lm.licenses.edit',
            ]);
        });
    });

    Route::group(['prefix' => 'update-downloads', 'as' => 'lm.update-downloads.'], function (): void {
        Route::match(['POST', 'GET'], '', [UpdateDownloadController::class, 'index'])->name('index');

        Route::delete('{updateDownload}', [UpdateDownloadController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'activity-logs', 'as' => 'lm.activity-logs.'], function (): void {
        Route::match(['POST', 'GET'], '', [ActivityLogController::class, 'index'])->name('index');

        Route::delete('{activityLog}', [ActivityLogController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'products', 'as' => 'lm.products.'], function (): void {
        Route::resource('/', ProductController::class)->parameters(['' => 'product']);

        Route::get('edit/{product}', [ProductController::class, 'edit'])->name('edit');

        Route::get('{product}', [ProductController::class, 'show'])->name('show');

        Route::post('edit/{product}', [ProductController::class, 'update'])->name('edit.update');

        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');

        Route::get('{product}/defaults', [
            'as' => 'defaults',
            'uses' => ProductDefaultsController::class,
            'permission' => 'lm.licenses.create',
        ])->where('product', '[A-Za-z0-9_-]+');

        Route::group(['prefix' => '{product}/versions', 'as' => 'versions.'], function (): void {
            Route::resource('', ProductVersionController::class)->parameters(['' => 'version'])->except(['destroy', 'show']);
            Route::get('{version}', [ProductVersionController::class, 'show'])->name('show');
            Route::get('edit/{version}', [ProductVersionController::class, 'edit'])->name('edit');
            Route::post('edit/{version}', [ProductVersionController::class, 'update'])->name('edit.update');

            Route::get('download-files/{version}', [ProductVersionController::class, 'downloadFiles'])->name('download-files');
            Route::get('download-sql/{version}', [ProductVersionController::class, 'downloadSql'])->name('download-sql');
            Route::delete('{version}', [ProductVersionController::class, 'destroy'])->name('destroy');
        })->resourceParameters(['' => 'product']);

        Route::group(['prefix' => '{product}/published-versions/{version}', 'as' => 'published-versions.', 'lm.products.versions.edit'], function (): void {
            Route::post('/', [PublishedVersionController::class, 'store'])->name('store');

            Route::delete('/', [PublishedVersionController::class, 'destroy'])->name('destroy');
        });
    });

    Route::match(['GET', 'POST'], 'generate-helper', [
        'as' => 'lm.generate-helper',
        'uses' => GenerateHelperController::class . '@index',
    ]);

    Route::get('manual-cron', [
        'as' => 'lm.manual-cron',
        'uses' => ManualCronController::class . '@index',
    ]);

    Route::post('manual-cron/run', [
        'as' => 'lm.manual-cron.run',
        'uses' => ManualCronController::class . '@run',
    ]);

    Route::match(['GET', 'POST'], 'php-obfuscator', [
        'as' => 'lm.php-obfuscator',
        'uses' => PhpObfuscatorController::class . '@index',
    ]);

    Route::group(['prefix' => 'widgets', 'as' => 'lm.widgets.', 'permission' => false], function (): void {
        Route::get('licenses-share', [DashboardWidgetController::class, 'licensesShare'])->name('licenses-share');
        Route::get('licenses-activations-chart', [DashboardWidgetController::class, 'licensesActivationsChart'])->name('licenses-activations-chart');
        Route::get('recent-activities', [DashboardWidgetController::class, 'recentActivities'])->name('recent-activities');
        Route::get('top-customers', [DashboardWidgetController::class, 'topCustomers'])->name('top-customers');
        Route::get('top-products', [DashboardWidgetController::class, 'topProducts'])->name('top-products');
    });

    Route::group(['prefix' => 'settings', 'as' => 'lm.'], function (): void {
        Route::group([
            'prefix' => 'general',
            'as' => 'general.',
        ], function (): void {
            Route::get('', [
                'uses' => GeneralSettingController::class . '@edit',
                'as' => 'settings',
            ]);

            Route::put('', [
                'uses' => GeneralSettingController::class . '@update',
                'as' => 'settings.update',
                'permission' => 'lm.general.settings',
            ]);

            Route::post('regenerate-encryption-key', [
                'uses' => GeneralSettingController::class . '@regenerateEncryptionKey',
                'as' => 'settings.regenerate-encryption-key',
                'permission' => 'lm.general.settings',
            ]);
        });

        Route::group([
            'prefix' => 'api-keys',
            'as' => 'api-keys.',
            'permission' => 'lm.api_keys.settings',
        ], function (): void {
            Route::get('', fn () => redirect()->route('lm.api.settings'))->name('index');
            Route::resource('', ApiKeyController::class)->parameters(['' => 'apiKey'])->except(['index']);
        });

        Route::group([
            'prefix' => 'api',
            'as' => 'api.',
        ], function (): void {
            Route::get('', [
                'uses' => ApiSettingController::class . '@edit',
                'as' => 'settings',
            ]);

            Route::put('', [
                'uses' => ApiSettingController::class . '@update',
                'as' => 'settings.update',
                'permission' => 'lm.api.settings',
            ]);

            Route::get('sample-app/download', [
                'as' => 'sample-app.download',
                'uses' => SampleAppDownloadController::class,
                'permission' => 'lm.api.settings',
            ]);
        });

        Route::group([
            'prefix' => 'legacy-migration',
            'as' => 'legacy-migration.',
        ], function (): void {
            Route::get('', [
                'uses' => LegacyMigrationController::class . '@edit',
                'as' => 'settings',
            ]);

            Route::put('', [
                'uses' => LegacyMigrationController::class . '@update',
                'as' => 'settings.update',
                'permission' => 'lm.legacy-migration.settings',
            ]);

            Route::post('migrate', [
                'uses' => LegacyMigrationController::class . '@migrate',
                'as' => 'migrate',
                'permission' => 'lm.legacy-migration.settings',
            ]);

            Route::delete('delete', [
                'uses' => LegacyMigrationController::class . '@deleteTables',
                'as' => 'delete',
                'permission' => 'lm.legacy-migration.settings',
            ]);

            Route::get('status', [
                'uses' => LegacyMigrationController::class . '@status',
                'as' => 'status',
                'permission' => 'lm.legacy-migration.settings',
            ]);
        });
    });

    Route::prefix('tools/data-synchronize')->name('tools.data-synchronize.')->group(function (): void {
        Route::prefix('export')->name('export.')->group(function (): void {
            Route::group(['prefix' => 'licenses', 'as' => 'licenses.', 'permission' => 'lm.licenses.export'], function (): void {
                Route::get('/', [ExportLicenseController::class, 'index'])->name('index');
                Route::post('/', [ExportLicenseController::class, 'store'])->name('store');
            });
        });

        Route::prefix('import')->name('import.')->group(function (): void {
            Route::group(['prefix' => 'licenses', 'as' => 'licenses.', 'permission' => 'lm.licenses.import'], function (): void {
                Route::get('/', [ImportLicenseController::class, 'index'])->name('index');
                Route::post('/', [ImportLicenseController::class, 'import'])->name('store');
                Route::post('validate', [ImportLicenseController::class, 'validateData'])->name('validate');
                Route::post('download-example', [ImportLicenseController::class, 'downloadExample'])->name('download-example');
            });
        });
    });

}); // End of license-manager prefix group
