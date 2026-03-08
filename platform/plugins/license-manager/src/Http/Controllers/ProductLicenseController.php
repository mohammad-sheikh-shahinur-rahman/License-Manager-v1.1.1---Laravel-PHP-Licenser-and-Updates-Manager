<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\ProductLicenseForm;
use Botble\LicenseManager\Http\Requests\ProductLicenseRequest;
use Botble\LicenseManager\Mail\LicenseDetailsMail;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Services\BulkLicenseGenerationService;
use Botble\LicenseManager\Tables\ProductLicenseTable;
use Illuminate\Support\Facades\Mail;

class ProductLicenseController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.licenses.title'), route('lm.licenses.index'));
    }

    public function index(ProductLicenseTable $licenseTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.licenses.title'));

        return $licenseTable->renderTable();
    }

    public function create(ProductLicenseForm $licenseForm)
    {
        $this->pageTitle(trans('core/base::forms.create'));

        return $licenseForm->create()->renderForm();
    }

    public function store(ProductLicenseRequest $request)
    {
        $product = Product::query()->where('reference_id', $request->input('product_reference_id'))->firstOrFail();

        $data = $request->validated();
        $data['is_envato'] = ! empty($product->envato_id);
        $data['domains'] = $data['domains'] ?? null;
        $data['ips'] = $data['ips'] ?? null;

        $isBulk = (bool) ($data['bulk_create'] ?? false);
        $bulkQuantity = (int) ($data['bulk_quantity'] ?? 2);

        unset($data['bulk_create'], $data['bulk_quantity']);

        if ($isBulk) {
            return $this->storeBulk($data, $bulkQuantity, $product);
        }

        $license = ProductLicense::query()->create($data);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_created_by_admin', [
            'license' => e($license->license_code),
            'product' => e($product->name),
        ]));

        return $this
            ->httpResponse()
            ->setNextRoute('lm.licenses.edit', $license)
            ->withCreatedSuccessMessage();
    }

    protected function storeBulk(array $data, int $quantity, Product $product): BaseHttpResponse
    {
        $licenses = app(BulkLicenseGenerationService::class)
            ->generate($product, $quantity, $data);

        return $this
            ->httpResponse()
            ->setNextRoute('lm.licenses.index')
            ->setMessage(trans('plugins/license-manager::license-manager.product_license.bulk_created', [
                'quantity' => $licenses->count(),
            ]));
    }

    public function edit(ProductLicense $license, ProductLicenseForm $licenseForm)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $license->license_code]));

        return $licenseForm->createFromModel($license)->renderForm();
    }

    public function update(ProductLicense $license, ProductLicenseRequest $request)
    {
        $data = $request->validated();
        $data['domains'] = $data['domains'] ?? null;
        $data['ips'] = $data['ips'] ?? null;

        $license->update($data);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_updated_by_admin', ['license' => e($license->license_code)]));

        return $this
            ->httpResponse()
            ->setNextRoute('lm.licenses.edit', $license)
            ->setPreviousRoute('lm.licenses.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(ProductLicense $license)
    {
        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_deleted_by_admin', ['license' => e($license->license_code)]));

        return DeleteResourceAction::make($license);
    }

    public function sendEmail(ProductLicense $license): BaseHttpResponse
    {
        if (! $license->email) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/license-manager::license-manager.emails.send_license_email_no_email'));
        }

        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown_product');

        Mail::to($license->email)->queue(new LicenseDetailsMail($license, $productName));

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.license_details_email_sent', [
                'email' => e($license->email),
                'license' => e($license->license_code),
                'product' => e($productName),
            ]),
        ]);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/license-manager::license-manager.emails.send_license_email_success', [
                'email' => $license->email,
            ]));
    }
}
