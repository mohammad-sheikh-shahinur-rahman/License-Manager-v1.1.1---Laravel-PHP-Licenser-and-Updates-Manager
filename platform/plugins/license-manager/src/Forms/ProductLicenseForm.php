<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TagFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TagField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Actions\LicenseCode\GenerateLicenseCode;
use Botble\LicenseManager\Http\Requests\ProductLicenseRequest;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;

class ProductLicenseForm extends FormAbstract
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
        $preEnableBulk = request()->query('bulk');

        $defaultsUrl = route('lm.products.defaults', ['product' => '__PRODUCT__']);

        Assets::addScriptsDirectly('vendor/core/plugins/license-manager/js/bulk-license-defaults.js');

        $this
            ->setupModel(new ProductLicense())
            ->setValidatorClass(ProductLicenseRequest::class)
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
                'license_code',
                TextField::class,
                TextFieldOption::make()
                    ->required()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.license_code'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.license_code_placeholder'))
                    ->when(
                        ! $this->getModel()->exists,
                        fn (TextFieldOption $option) => $option->defaultValue(GenerateLicenseCode::make()->handle())
                    )
            )
            ->add(
                'license_type',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.license_type'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.license_type_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.license_type_helper'))
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
                'uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.uses_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.uses_helper'))
            )
            ->add(
                'parallel_uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.parallel_uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.parallel_uses_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.parallel_uses_helper'))
            )
            ->add(
                'expires_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.expiry'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.expiry_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'expiry_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.expiry_days'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.expiry_days_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.expiry_days_helper'))
            )
            ->add(
                'updates_until',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.updates_till'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.updates_till_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'support_until',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.supported_till'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.supported_till_helper'))
                    ->defaultValue(null)
                    ->withTimePicker()
            )
            ->add(
                'domains',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.domains'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.domains_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.domains_helper'))
                    ->value($this->toTagifyFormat($this->getModel()?->domains))
            )
            ->add(
                'ips',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.ips'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.ips_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.ips_helper'))
                    ->value($this->toTagifyFormat($this->getModel()?->ips))
            )
            ->add(
                'comments',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.comments'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product_license.form.comments_placeholder'))
                    ->colspan(2),
            )
            ->add(
                'is_valid',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_license.form.validity'))
                    ->helperText(trans('plugins/license-manager::license-manager.product_license.form.validity_helper'))
                    ->colspan(2)
                    ->when(
                        $this->getModel()->exists,
                        fn (OnOffFieldOption $option) => $option->value(! $this->getModel()->is_valid)
                    ),
            )
            ->when(! $this->getModel()->exists, function (ProductLicenseForm $form) use ($preEnableBulk): void {
                $form
                    ->add(
                        'bulk_create',
                        OnOffCheckboxField::class,
                        OnOffFieldOption::make()
                            ->label(trans('plugins/license-manager::license-manager.product_license.form.bulk_create'))
                            ->helperText(trans('plugins/license-manager::license-manager.product_license.form.bulk_create_helper'))
                            ->colspan(2)
                            ->when(
                                $preEnableBulk,
                                fn (OnOffFieldOption $option) => $option->defaultValue(true)
                            )
                    )
                    ->addOpenCollapsible('bulk_create', '1', '0')
                    ->add(
                        'bulk_quantity',
                        NumberField::class,
                        NumberFieldOption::make()
                            ->label(trans('plugins/license-manager::license-manager.product_license.form.bulk_quantity'))
                            ->required()
                            ->defaultValue(2)
                    )
                    ->addCloseCollapsible('bulk_create', '1')
                    ->add(
                        'bulk_create_script',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<script>$(function(){var $c=$("input[name=bulk_create][type=checkbox]"),$l=$("[name=license_code]").closest(".mb-3");$c.on("change",function(){$c.is(":checked")?$l.slideUp():$l.slideDown()});if($c.is(":checked"))$l.hide()});</script>')
                            ->toArray()
                    );
            });
    }

    protected function toTagifyFormat(array|string|null $items): ?string
    {
        if (empty($items)) {
            return null;
        }

        if (is_string($items)) {
            $items = json_decode($items, true) ?: [$items];
        }

        return json_encode(array_map(fn ($item) => ['value' => $item], $items));
    }
}
