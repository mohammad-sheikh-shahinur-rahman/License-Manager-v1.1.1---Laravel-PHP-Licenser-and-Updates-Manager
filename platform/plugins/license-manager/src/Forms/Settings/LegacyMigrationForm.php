<?php

namespace Botble\LicenseManager\Forms\Settings;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\TextField;
use Botble\LicenseManager\Http\Requests\Settings\LegacyMigrationRequest;
use Botble\LicenseManager\Services\LicenseBoxMigrationService;
use Botble\Setting\Forms\SettingForm;

class LegacyMigrationForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $migrationService = app(LicenseBoxMigrationService::class);

        $this
            ->setValidatorClass(LegacyMigrationRequest::class)
            ->setSectionTitle(PageTitle::getTitle(false))
            ->setSectionDescription(trans('plugins/license-manager::license-manager.legacy_migration.description'))
            ->setFormOption('id', 'legacy-migration-form')
            ->add(
                'lm_legacy_encryption_key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.legacy_migration.legacy_encryption_key'))
                    ->placeholder(trans('plugins/license-manager::license-manager.legacy_migration.legacy_encryption_key_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.legacy_migration.legacy_encryption_key_helper'))
                    ->value(setting('lm_legacy_encryption_key'))
                    ->toArray()
            )
            ->add(
                'legacy_migration_panel',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('plugins/license-manager::settings.partials.legacy-migration-panel', [
                        'hasTables' => $migrationService->hasLegacyTables(),
                        'tables' => $migrationService->detectTables(),
                        'counts' => $migrationService->getRecordCounts(),
                    ])
                    ->toArray()
            );
    }
}
