<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\EditorFieldOption;
use Botble\Base\Forms\FieldOptions\LabelFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\LabelField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Http\Requests\ProductVersionRequest;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Support\Str;

class ProductVersionForm extends FormAbstract
{
    public function setup(): void
    {
        $product = request()->route('product');
        $model = $this->getModel() && $this->getModel()->exists ? $this->getModel() : null;

        $this
            ->model(ProductVersion::class)
            ->setValidatorClass(ProductVersionRequest::class)
            ->columns()
            ->when($model, function (FormAbstract $form): void {
                $form->add(
                    'warning_message',
                    AlertField::class,
                    AlertFieldOption::make()
                        ->colspan(2)
                        ->type('warning')
                        ->content(trans('plugins/license-manager::license-manager.product_version.form.warning_message', [
                            'upload_max_filesize' => ini_get('upload_max_filesize'),
                            'post_max_size' => ini_get('post_max_size'),
                        ]))
                );
            }, function (FormAbstract $form): void {
                $form->add(
                    'version_id',
                    TextField::class,
                    TextFieldOption::make()
                        ->required()
                        ->colspan(2)
                        ->defaultValue(hash('sha1', (string) Str::ulid()))
                        ->label(trans('plugins/license-manager::license-manager.product_version.form.version_id'))
                );
            })
            ->add(
                'version',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.version'))
                    ->required()
                    ->colspan(2)
                    ->helperText(trans('plugins/license-manager::license-manager.product_version.form.version_helper'))
            )
            ->add(
                'released_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.release_date'))
                    ->colspan(2)
                    ->required(),
            )
            ->add(
                'summary',
                TextField::class,
                TextFieldOption::make()
                    ->colspan(2)
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.summary'))
            )
            ->add(
                'changelog',
                EditorField::class,
                EditorFieldOption::make()
                    ->colspan(2)
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.changelog'))
                    ->required()
            )
            ->add(
                'main_file',
                'file',
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.main_file'))
                    ->attributes([
                        'accept' => implode(',', Helper::getSupportedFileTypes()),
                    ])
                    ->when(
                        $model && $model->main_file,
                        fn (TextFieldOption $option) => $option->helperText(trans('plugins/license-manager::license-manager.product_version.form.file_exist_helper', [
                            'link' => Html::link(route('lm.products.versions.download-files', [$product, $model])),
                        ])),
                        fn (TextFieldOption $option) => $option->required()
                    )
            )
            ->add(
                'sql_file',
                'file',
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.sql_file'))
                    ->attributes([
                        'accept' => implode(',', Helper::getSupportedFileTypes()),
                    ])
                    ->when($model && $model->sql_file, function (TextFieldOption $option) use ($product, $model): void {
                        $option->helperText(
                            trans('plugins/license-manager::license-manager.product_version.form.file_exist_helper', [
                                'link' => Html::link(route('lm.products.versions.download-sql', [$product, $model])),
                            ])
                        );
                    })
            )
            ->add(
                'file_exist_helper',
                LabelField::class,
                LabelFieldOption::make()
                    ->colspan(2)
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.file_helper', ['extensions' => implode(', ', Helper::getSupportedFileTypes())]))
            )
            ->add(
                'is_active',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.product_version.form.status'))
                    ->colspan(2)
                    ->helperText(trans('plugins/license-manager::license-manager.product_version.form.status_helper'))
                    ->defaultValue(true)
            );
    }
}
