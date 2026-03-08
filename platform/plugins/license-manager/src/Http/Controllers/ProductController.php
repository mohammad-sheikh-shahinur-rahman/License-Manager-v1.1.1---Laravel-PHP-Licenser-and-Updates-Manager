<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\ProductForm;
use Botble\LicenseManager\Http\Requests\ProductRequest;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Tables\ProductTable;
use Botble\LicenseManager\Tables\ProductVersionTable;
use Illuminate\Support\Arr;

class ProductController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.products.title'), route('lm.products.index'));
    }

    public function index(ProductTable $productTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.products.title'));

        return $productTable->renderTable();
    }

    public function create(ProductForm $productForm)
    {
        $this->pageTitle(trans('core/base::forms.create'));

        return $productForm->create()->renderForm();
    }

    public function store(ProductRequest $request)
    {
        $product = Product::query()->create($request->validated());

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_created_by_admin', ['product' => e($product->name)]));

        return $this
            ->httpResponse()
            ->setNextRoute('lm.products.edit', $product->id)
            ->withCreatedSuccessMessage();
    }

    public function show(Product $product, ProductVersionTable $productVersionTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.product.viewing', ['name' => $product->name]));

        $productVersionTable
            ->product($product)
            ->setAjaxUrl(route('lm.products.versions.index', $product));

        return view('plugins/license-manager::products.show', compact('product', 'productVersionTable'));
    }

    public function edit(Product $product, ProductForm $productForm)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $product->name]));

        return $productForm->createFromModel($product)->renderForm();
    }

    public function update(Product $product, ProductRequest $request)
    {
        $product->update(Arr::except($request->validated(), 'reference_id'));

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_updated_by_admin', ['product' => e($product->name)]));

        return $this
            ->httpResponse()
            ->setNextRoute('lm.products.edit', $product->id)
            ->setPreviousRoute('lm.products.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Product $product)
    {
        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.product_deleted_by_admin', ['product' => e($product->name)]));

        return DeleteResourceAction::make($product);
    }
}
