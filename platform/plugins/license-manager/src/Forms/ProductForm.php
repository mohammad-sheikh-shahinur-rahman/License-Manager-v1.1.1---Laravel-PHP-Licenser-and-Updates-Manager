<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Actions\Products\GenerateProductUniqueId;
use Botble\LicenseManager\Http\Requests\ProductRequest;
use Botble\LicenseManager\Models\Product;

class ProductForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Product())
            ->setValidatorClass(ProductRequest::class)
            ->columns()
            ->setBreakFieldPoint('is_active')
            ->add(
                'reference_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.unique_id'))
                    ->when(
                        $this->getModel()->exists,
                        fn (TextFieldOption $option): TextFieldOption => $option->disabled(),
                        fn (TextFieldOption $option): TextFieldOption => $option->defaultValue(
                            (new GenerateProductUniqueId())->handle()
                        )
                    )
                    ->required()
            )
            ->when(setting('lm_enable_envato_integration') || setting('envato_personal_token'), function (ProductForm $form): void {
                $form->add(
                    'envato_id',
                    TextField::class,
                    TextFieldOption::make()
                        ->label(trans('plugins/license-manager::license-manager.product.envato_id'))
                        ->placeholder(trans('plugins/license-manager::license-manager.product.envato_id_placeholder'))
                        ->helperText(trans('plugins/license-manager::license-manager.product.envato_id_helper'))
                );
            })
            ->add(
                'name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.product_name'))
                    ->colspan(2)
                    ->required()
            )
            ->add(
                'description',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.product_details'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.product_details_placeholder'))
                    ->rows(5)
                    ->colspan(2)
            )
            ->add(
                'license_update',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.license_update'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.license_update_helper'))
                    ->defaultValue(true)
            )
            ->add(
                'serve_latest_updates',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.serve_latest_updates'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.serve_latest_updates_helper'))
                    ->defaultValue(true)
            )
            ->add(
                'is_active',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/base::tables.status'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.product_status_helper'))
                    ->required()
                    ->choices([
                        1 => trans('plugins/license-manager::license-manager.activations.active'),
                        0 => trans('plugins/license-manager::license-manager.activations.inactive'),
                    ])
            )
            ->add(
                'license_defaults_heading',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(
                        '<h4 class="mt-3">' . trans('plugins/license-manager::license-manager.product.defaults_section_title') . '</h4>'
                        . '<p class="text-muted">' . trans('plugins/license-manager::license-manager.product.defaults_section_description') . '</p>'
                    )
                    ->toArray()
            )
            ->add(
                'default_license_type',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_license_type'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_license_type_placeholder'))
            )
            ->add(
                'default_uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_uses_placeholder'))
            )
            ->add(
                'default_parallel_uses',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_parallel_uses'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_parallel_uses_placeholder'))
            )
            ->add(
                'default_expiry_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_expiry_days'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_expiry_days_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.default_expiry_days_helper'))
            )
            ->add(
                'default_updates_until_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_updates_until_days'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_updates_until_days_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.default_updates_until_days_helper'))
            )
            ->add(
                'default_support_until_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_support_until_days'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_support_until_days_placeholder'))
                    ->helperText(trans('plugins/license-manager::license-manager.product.default_support_until_days_helper'))
            )
            ->add(
                'default_comments',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product.default_comments'))
                    ->placeholder(trans('plugins/license-manager::license-manager.product.default_comments_placeholder'))
                    ->colspan(2)
            )
        ;
    }
}
