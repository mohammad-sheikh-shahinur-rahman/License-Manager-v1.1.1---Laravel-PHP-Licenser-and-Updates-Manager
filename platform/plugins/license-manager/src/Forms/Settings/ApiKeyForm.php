<?php

namespace Botble\LicenseManager\Forms\Settings;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\MultiChecklistFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Enums\ApiExternalScope;
use Botble\LicenseManager\Enums\ApiInternalScope;
use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Http\Requests\Settings\ApiKeyRequest;
use Botble\LicenseManager\Models\ApiKey;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class ApiKeyForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(ApiKey::class)
            ->setValidatorClass(ApiKeyRequest::class);

        $apiKeyType = $this->getModel()->type ?? ApiKeyType::External;

        $this
            ->add(
                'key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_key.key'))
                    ->when(
                        $this->getModel()->exists,
                        fn (TextFieldOption $option) => $option->disabled(),
                        fn (TextFieldOption $option) => $option->defaultValue((string) Str::ulid())->required()
                    )
            )
            ->add(
                'type',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_key.type'))
                    ->helperText(trans('plugins/license-manager::license-manager.api_key.type_helper'))
                    ->choices(ApiKeyType::labels())
                    ->defaultValue(ApiKeyType::External->value)
                    ->selected($apiKeyType->value)
            )
            ->add(
                'external_scopes[]',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_key.scopes'))
                    ->choices(ApiExternalScope::choices())
                    ->selected($apiKeyType === ApiKeyType::External ? ($this->getModel()->scopes ?? []) : [])
                    ->collapsible('type', $external = ApiKeyType::External->value, $currentType = $apiKeyType ? $apiKeyType->value : $external)
            )
            ->add(
                'internal_scopes[]',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_key.scopes'))
                    ->choices(ApiInternalScope::choices())
                    ->selected($apiKeyType === ApiKeyType::Internal ? ($this->getModel()->scopes ?? []) : [])
                    ->collapsible('type', ApiKeyType::Internal->value, $currentType)
            )
            ->add(
                'special',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->helperText(trans('plugins/license-manager::license-manager.api_key.special_helper'))
                    ->label(trans('plugins/license-manager::license-manager.api_key.special'))
            )
            ->add(
                'revoked',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->helperText(trans('plugins/license-manager::license-manager.api_key.revoke_helper'))
                    ->label(trans('plugins/license-manager::license-manager.api_key.revoke'))
            )
            ->add(
                'has_expired',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_key.has_expired'))
                    ->value($hasExpired = $this->getModel()->expires_at !== null ? '1' : '0')
            )
            ->add(
                'expires_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->helperText(trans('plugins/license-manager::license-manager.api_key.expires_at_helper'))
                    ->label(trans('plugins/license-manager::license-manager.api_key.expires_at'))
                    ->defaultValue(Date::now()->addMonths(3))
                    ->collapsible('has_expired', '1', $hasExpired)
            )
        ;
    }

    public function getRequestData(): array
    {
        $data = $this->request->validated();

        if ($this->getModel()->exists) {
            unset($data['key']);
        }

        if ($data['type'] === ApiKeyType::Internal->value) {
            $data['scopes'] = $data['internal_scopes'] ?? [];
        } else {
            $data['scopes'] = $data['external_scopes'] ?? [];
        }

        unset($data['internal_scopes']);

        unset($data['external_scopes']);

        if ($data['has_expired'] === '1') {
            $data['expires_at'] = Date::parse($data['expires_at'])->toDateTimeString();
        } else {
            $data['expires_at'] = null;
        }

        unset($data['has_expired']);

        return $data;
    }
}
