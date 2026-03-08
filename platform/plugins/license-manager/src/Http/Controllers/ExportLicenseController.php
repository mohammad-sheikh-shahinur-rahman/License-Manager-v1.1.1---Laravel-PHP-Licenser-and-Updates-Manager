<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\DataSynchronize\Exporter\Exporter;
use Botble\DataSynchronize\Http\Controllers\ExportController;
use Botble\DataSynchronize\Http\Requests\ExportRequest;
use Botble\LicenseManager\Exporters\LicenseExporter;

class ExportLicenseController extends ExportController
{
    protected function getExporter(): Exporter
    {
        $exporter = LicenseExporter::make();

        if (request()->has('product_id') && request()->input('product_id') !== '') {
            $exporter->setProductId(request()->input('product_id'));
        }

        if (request()->has('is_valid') && request()->input('is_valid') !== '') {
            $exporter->setIsValid(request()->boolean('is_valid'));
        }

        if (request()->has(['start_date', 'end_date'])) {
            $exporter->setDateRange(
                request()->input('start_date'),
                request()->input('end_date')
            );
        }

        return $exporter;
    }

    public function store(ExportRequest $request)
    {
        $request->validate([
            'product_id' => ['nullable', 'exists:lm_products,id'],
            'is_valid' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        return parent::store($request);
    }
}
