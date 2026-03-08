<?php

namespace Botble\LicenseManager\Http\Controllers\Settings;

use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\Settings\LegacyMigrationForm;
use Botble\LicenseManager\Http\Requests\Settings\LegacyMigrationRequest;
use Botble\LicenseManager\Services\LicenseBoxMigrationService;
use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Http\JsonResponse;

class LegacyMigrationController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.title'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.legacy_migration.title'));

        return LegacyMigrationForm::create()->renderForm();
    }

    public function update(LegacyMigrationRequest $request)
    {
        return $this->performUpdate($request->validated());
    }

    public function migrate(LicenseBoxMigrationService $migrationService): JsonResponse
    {
        if (! $migrationService->hasLegacyTables()) {
            return response()->json([
                'success' => false,
                'message' => trans('plugins/license-manager::license-manager.legacy_migration.no_tables_found'),
            ]);
        }

        $step = request()->input('step', 'products');
        $offset = (int) request()->input('offset', 0);

        $result = $migrationService->migrateStep($step, $offset);

        return response()->json($result);
    }

    public function deleteTables(LicenseBoxMigrationService $migrationService): JsonResponse
    {
        $deleted = $migrationService->deleteLegacyTables();

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? trans('plugins/license-manager::license-manager.legacy_migration.tables_deleted')
                : trans('plugins/license-manager::license-manager.legacy_migration.tables_delete_failed'),
        ]);
    }

    public function status(LicenseBoxMigrationService $migrationService): JsonResponse
    {
        return response()->json([
            'has_tables' => $migrationService->hasLegacyTables(),
            'tables' => $migrationService->detectTables(),
            'counts' => $migrationService->getRecordCounts(),
        ]);
    }
}
