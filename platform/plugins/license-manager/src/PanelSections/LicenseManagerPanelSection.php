<?php

namespace Botble\LicenseManager\PanelSections;

use Botble\Base\PanelSections\PanelSection;
use Botble\Base\PanelSections\PanelSectionItem;

class LicenseManagerPanelSection extends PanelSection
{
    public function setup(): void
    {
        $this
            ->setId('license-manager')
            ->setTitle(trans('plugins/license-manager::license-manager.title'))
            ->withPriority(99990)
            ->addItems([
                PanelSectionItem::make('lm_general')
                    ->setTitle(trans('plugins/license-manager::license-manager.general.title'))
                    ->withDescription(trans('plugins/license-manager::license-manager.general.description'))
                    ->withIcon('ti ti-settings')
                    ->withPriority(10)
                    ->withRoute('lm.general.settings'),
                PanelSectionItem::make('lm_api_settings')
                    ->setTitle(trans('plugins/license-manager::license-manager.api_setting.title'))
                    ->withDescription(trans('plugins/license-manager::license-manager.api_setting.description'))
                    ->withIcon('ti ti-api')
                    ->withPriority(100)
                    ->withRoute('lm.api.settings'),
                PanelSectionItem::make('lm_legacy_migration')
                    ->setTitle(trans('plugins/license-manager::license-manager.legacy_migration.title'))
                    ->withDescription(trans('plugins/license-manager::license-manager.legacy_migration.description'))
                    ->withIcon('ti ti-database-import')
                    ->withPriority(200)
                    ->withRoute('lm.legacy-migration.settings'),
                PanelSectionItem::make('lm_export_licenses')
                    ->setTitle(trans('plugins/license-manager::license-manager.export.name'))
                    ->withDescription(trans('plugins/license-manager::license-manager.export.description'))
                    ->withIcon('ti ti-file-export')
                    ->withPriority(300)
                    ->withPermission('lm.licenses.export')
                    ->withRoute('tools.data-synchronize.export.licenses.index'),
                PanelSectionItem::make('lm_import_licenses')
                    ->setTitle(trans('plugins/license-manager::license-manager.import.name'))
                    ->withDescription(trans('plugins/license-manager::license-manager.import.description'))
                    ->withIcon('ti ti-file-import')
                    ->withPriority(310)
                    ->withPermission('lm.licenses.import')
                    ->withRoute('tools.data-synchronize.import.licenses.index'),
            ])
        ;
    }
}
