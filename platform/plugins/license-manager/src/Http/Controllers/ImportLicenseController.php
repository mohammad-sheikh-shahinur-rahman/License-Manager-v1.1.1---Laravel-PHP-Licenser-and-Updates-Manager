<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\DataSynchronize\Http\Controllers\ImportController;
use Botble\DataSynchronize\Importer\Importer;
use Botble\LicenseManager\Importers\LicenseImporter;

class ImportLicenseController extends ImportController
{
    protected function getImporter(): Importer
    {
        return LicenseImporter::make();
    }
}
