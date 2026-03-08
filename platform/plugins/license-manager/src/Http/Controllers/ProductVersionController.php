<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Actions\ProductVersions\StoreProductVersion;
use Botble\LicenseManager\Actions\ProductVersions\UpdateProductVersion;
use Botble\LicenseManager\Forms\ProductVersionForm;
use Botble\LicenseManager\Http\Requests\ProductVersionRequest;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\LicenseManager\Tables\ProductVersionTable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class ProductVersionController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        $product = request()->route('product');

        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.products.title'), route('lm.products.index'))
            ->add($product->name, route('lm.products.show', $product));
    }

    public function index(Product $product, ProductVersionTable $productVersionTable)
    {
        $this->pageTitle(trans(
            'plugins/license-manager::license-manager.product_version.manage_versions',
            ['name' => $product->name]
        ));

        return $productVersionTable
            ->product($product)
            ->renderTable();
    }

    public function create(Product $product)
    {
        $this->pageTitle(trans(
            'plugins/license-manager::license-manager.product_version.create',
            ['product_reference_id' => $product->name]
        ));

        return ProductVersionForm::create()->renderForm();
    }

    public function store(Product $product, ProductVersionRequest $request, StoreProductVersion $storeProductVersion)
    {
        $form = ProductVersionForm::create()
            ->setRequest($request)
            ->onlyValidatedData();

        $productVersion = null;

        $form->saving(function (ProductVersionForm $form) use ($product, $storeProductVersion, &$productVersion): void {
            $productVersion = $storeProductVersion->handle($product, $form->getRequestData());
        });

        return $this
            ->httpResponse()
            ->withCreatedSuccessMessage()
            ->setNextUrl(route('lm.products.versions.edit', [$product, $productVersion]));
    }

    public function show(Product $product, ProductVersion $version)
    {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        $this->pageTitle(trans(
            'plugins/license-manager::license-manager.product_version.view',
            ['product' => $product->name, 'version' => $version->version]
        ));

        return view('plugins/license-manager::admin.product-versions.show', compact('product', 'version'));
    }

    public function edit(Product $product, ProductVersion $version)
    {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        $this->pageTitle(trans(
            'plugins/license-manager::license-manager.product_version.edit',
            ['product' => $product->name, 'version' => $version->version]
        ));

        return ProductVersionForm::createFromModel($version)->renderForm();
    }

    public function update(
        Product $product,
        ProductVersion $version,
        ProductVersionRequest $request,
        UpdateProductVersion $updateProductVersion
    ) {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        $form = ProductVersionForm::createFromModel($version)
            ->setRequest($request)
            ->onlyValidatedData();

        $form->saving(function (ProductVersionForm $form) use ($product, $version, $updateProductVersion): void {
            $updateProductVersion->handle($product, $version, $form->getRequestData());
        });

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage()
            ->setNextUrl(route('lm.products.versions.edit', [$product, $version]));
    }

    public function destroy(Product $product, ProductVersion $version)
    {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        return DeleteResourceAction::make($version);
    }

    public function downloadFiles(Product $product, ProductVersion $version)
    {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        $file = storage_path('app/version-files/' . $product->reference_id . '/' . $version->main_file);

        abort_unless(File::exists($file), 404);

        return response()->download(
            $file,
            sprintf('%s_%s.zip', $version->versionProduct->name, $version->version),
            ['Content-Type' => 'application/zip']
        );
    }

    public function downloadSql(Product $product, ProductVersion $version)
    {
        Gate::allowIf($version->product_reference_id === $product->reference_id);

        abort_unless($version->sql_file, 404);

        $file = storage_path('app/version-files/' . $product->reference_id . '/' . $version->sql_file);

        abort_unless(File::exists($file), 404);

        return response()->download(
            $file,
            sprintf('%s_%s.sql', $version->versionProduct->name, $version->version),
            ['Content-Type' => 'text/plain']
        );
    }
}
