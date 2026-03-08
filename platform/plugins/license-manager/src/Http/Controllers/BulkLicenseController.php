<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\BulkLicenseForm;
use Botble\LicenseManager\Http\Requests\BulkLicenseRequest;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Services\BulkLicenseGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BulkLicenseController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.bulk_generate.title'), route('lm.bulk-generate.index'));
    }

    public function index(BulkLicenseForm $form)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.bulk_generate.title'));

        return $form->create()->renderForm();
    }

    public function store(BulkLicenseRequest $request): RedirectResponse
    {
        $product = Product::query()
            ->where('reference_id', $request->input('product_reference_id'))
            ->firstOrFail();

        $overrides = $request->only([
            'type',
            'uses',
            'parallel_uses',
            'expiry_days',
            'expires_at',
            'updates_until',
            'support_until',
            'customer_id',
            'email',
            'invoice',
            'comments',
        ]);

        $licenses = app(BulkLicenseGenerationService::class)
            ->generate($product, (int) $request->input('quantity'), $overrides);

        $first = $licenses->first();

        session()->flash('bulk_generated_licenses', $licenses->pluck('license_code')->all());
        session()->flash('bulk_generated_product', [
            'name' => $product->name,
            'reference_id' => $product->reference_id,
        ]);
        session()->flash('bulk_generated_license_info', [
            'type' => $first->type,
            'uses' => $first->uses,
            'parallel_uses' => $first->parallel_uses,
            'expires_at' => $first->expires_at?->format('Y-m-d H:i'),
            'updates_until' => $first->updates_until?->format('Y-m-d H:i'),
            'support_until' => $first->support_until?->format('Y-m-d H:i'),
            'customer_id' => $first->customer_id,
            'email' => $first->email,
            'invoice' => $first->invoice,
        ]);

        return redirect()->route('lm.bulk-generate.success');
    }

    public function success(): View|RedirectResponse
    {
        $licenseCodes = session('bulk_generated_licenses', []);

        if (empty($licenseCodes)) {
            return redirect()->route('lm.bulk-generate.index');
        }

        $this->pageTitle(trans('plugins/license-manager::license-manager.bulk_generate.success_title'));

        return view('plugins/license-manager::bulk-generate.success', [
            'licenseCodes' => $licenseCodes,
            'product' => session('bulk_generated_product', []),
            'licenseInfo' => session('bulk_generated_license_info', []),
        ]);
    }
}
