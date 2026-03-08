<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Http\Requests\BulkLicenseRequest;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;

class BulkLicenseForm extends FormAbstract
{
    public function buildForm(): void
    {
        $products = Product::query()
            ->select(['reference_id', 'name', 'is_active'])
            ->get()
            ->mapWithKeys(function (Product $product): array {
                $status = $product->is_active
                    ? trans('plugins/license-manager::license-manager.activations.active')
                    : trans('plugins/license-manager::license-manager.activations.inactive');

                return [$product->reference_id => "{$product->name} ($status)"];
            })
            ->all();

        $preSelectedProduct = request()->query('product_reference_id');
        $defaultsUrl = route('lm.products.defaults', ['product' => '__PRODUCT__']);

        Assets::addScriptsDirectly('vendor/core/plugins/license-manager/js/bulk-license-defaults.js');

        $this
            ->setupModel(new ProductLicense())
            ->setValidatorClass(BulkLicenseRequest::class)
            ->setUrl(route('lm.bulk-generate.store'))
            ->columns()
            ->add(
                'product_reference_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.license_for_product'))
                    ->searchable()
                    ->required()
                    ->choices([
                        '' => trans('plugins/license-manager::license-manager.product_license.form.license_for_product_placeholder'),
                        ...$products,
                    ])
                    ->when(
                        $preSelectedProduct && isset($products[$preSelectedProduct]),
                        fn (SelectFieldOption $option) => $option->defaultValue($preSelectedProduct)
                    )
                    ->addAttribute('data-defaults-url', $defaultsUrl),
            )
            ->add(
                'quantity',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.bulk_generate.quantity'))
                    ->required()
                    ->defaultValue(10)
                    ->min(2)
                    ->max(500)
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.quantity_helper'))
            )
            ->add(
                'customer_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.client'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.client_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.client_helper'))
            )
            ->add(
                'email',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.client_email'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.client_email_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.client_email_helper'))
            )
            ->add(
                'invoice',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.invoice_number'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.invoice_number_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.invoice_number_helper'))
            )
            ->add(
                'license_type',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.license_type'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.license_type_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
            )
            ->add(
                'uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.uses_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
            )
            ->add(
                'parallel_uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.parallel_uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.parallel_uses_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
            )
            ->add(
                'expiry_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.expiry_days'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.expiry_days_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
            )
            ->add(
                'expires_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.expiry'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'updates_until',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.updates_till'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'support_until',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.supported_till'))
                    ->helperText(trans('plugins/license-manager::license-manager.bulk_generate.override_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'comments',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.comments'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.comments_placeholder'))
                    ->colspan(2)
            );
    }
}
