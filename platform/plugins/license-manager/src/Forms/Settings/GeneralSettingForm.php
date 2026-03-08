<?php

namespace Botble\LicenseManager\Forms\Settings;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\LicenseManager\Enums\EnvatoSite;
use Botble\LicenseManager\Http\Requests\Settings\GeneralSettingRequest;
use Botble\Setting\Forms\SettingForm;

class GeneralSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(GeneralSettingRequest::class)
            ->setSectionTitle(PageTitle::getTitle(false))
            ->setSectionDescription(
                trans('plugins/license-manager::license-manager.general.description')
            );

        $this
            ->add(
                'lm_license_code_format',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.license_code_format'))
                    ->choices([
                        'uuid' => trans('plugins/license-manager::license-manager.general.uuid', ['uuid' => 'CDE25DA0-04D7-4201-A580-D0906703B3E0']),
                        'ulid' => trans('plugins/license-manager::license-manager.general.ulid', ['ulid' => '1CBVEVGPKBTES73OAVIKW5']),
                        'random' => trans('plugins/license-manager::license-manager.general.random'),
                        'custom' => trans('plugins/license-manager::license-manager.general.custom'),
                    ])
                    ->selected($licenseFormat = setting('lm_license_code_format', 'uuid'))
            )
            ->add(
                'lm_license_code_custom_format',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.custom_license_code_format'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.custom_license_code_format_helper'))
                    ->value(setting('lm_license_code_custom_format', $defaultCustomFormat = '%Z%Z%Z%Z-%Z%Z%Z%Z-%Z%Z%Z%Z-%Z%Z%Z%Z'))
                    ->defaultValue($defaultCustomFormat)
                    ->collapsible('lm_license_code_format', 'custom', $licenseFormat)
            )
            ->add(
                'lm_license_code_random_length',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.random_license_code_length'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.random_license_code_length_helper'))
                    ->value(setting('lm_license_code_random_length', 20))
                    ->defaultValue(20)
                    ->collapsible('lm_license_code_format', 'random', $licenseFormat)
            )
            ->add(
                'lm_license_code_case_insensitive',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.license_code_case_insensitive'))
                    ->choices([
                        'uppercase' => trans('plugins/license-manager::license-manager.general.uppercase'),
                        'lowercase' => trans('plugins/license-manager::license-manager.general.lowercase'),
                        'mix' => trans('plugins/license-manager::license-manager.general.mix'),
                    ])
                    ->selected(setting('lm_license_code_case_insensitive', 'uppercase'))
            )
            ->add(
                'lm_license_encryption_cipher',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.license_encryption_cipher'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.license_encryption_cipher_helper'))
                    ->choices([
                        'aes-128-cbc' => 'AES-128-CBC',
                        'aes-256-cbc' => 'AES-256-CBC',
                        'aes-256-gcm' => 'AES-256-GCM',
                        'aes-128-gcm' => 'AES-128-GCM',
                    ])
                    ->selected(setting('lm_license_encryption_cipher', 'aes-128-cbc'))
            )
            ->add(
                'lm_license_encryption_key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.license_encryption_key'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.license_encryption_key_helper'))
                    ->value(setting('lm_license_encryption_key'))
                    ->when(setting('lm_license_encryption_key'), fn (TextFieldOption $option) => $option->disabled())
            )
            ->when(setting('lm_license_encryption_key'), function (): void {
                Assets::addScriptsDirectly('vendor/core/plugins/license-manager/js/regenerate-encryption-key.js');

                $regenerateRoute = route('lm.general.settings.regenerate-encryption-key');
                $modalId = 'modal-regenerate-encryption-key';
                $confirmTitle = e(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerate_confirm_title'));
                $confirmText = e(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerate_confirm'));
                $warningText = e(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerate_warning'));
                $helperText = e(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerate_helper'));
                $buttonLabel = e(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerate_button'));
                $cancelLabel = e(trans('core/base::forms.cancel'));
                $confirmPhrase = 'CONFIRM';
                $typeToProceedLabel = trans('plugins/license-manager::license-manager.general.type_to_proceed', ['phrase' => $confirmPhrase]);

                $html = <<<HTML
<div class="mb-3">
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#{$modalId}">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
        {$buttonLabel}
    </button>
    <div class="form-text text-danger mt-1">{$helperText}</div>
</div>

<div class="modal modal-blur fade" id="{$modalId}" tabindex="-1" role="dialog" aria-hidden="true"
    data-route="{$regenerateRoute}"
    data-confirm-phrase="{$confirmPhrase}">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{$confirmTitle}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{$confirmText}</p>
                <div class="alert alert-danger">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>
                        </div>
                        <div>{$warningText}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">{$typeToProceedLabel}</label>
                    <input type="text" class="form-control" id="regenerate-key-confirm-input" autocomplete="off" placeholder="{$confirmPhrase}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">{$cancelLabel}</button>
                <button type="button" class="btn btn-danger" id="regenerate-key-confirm-btn" data-button-label="{$buttonLabel}" disabled>{$buttonLabel}</button>
            </div>
        </div>
    </div>
</div>
HTML;

                $this->add(
                    'lm_license_encryption_key_regenerate',
                    HtmlField::class,
                    HtmlFieldOption::make()->content($html)->toArray()
                );
            })
            ->add(
                'lm_product_unique_id_format',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.product_unique_id_format'))
                    ->choices($formatUniqueIdChoices = [
                        'uuid' => trans('plugins/license-manager::license-manager.general.uuid', ['uuid' => 'CDE25DA0-04D7-4201-A580-D0906703B3E0']),
                        'ulid' => trans('plugins/license-manager::license-manager.general.ulid', ['ulid' => '1CBVEVGPKBTES73OAVIKW5']),
                        'hex8' => trans('plugins/license-manager::license-manager.general.hex8', ['hex8' => 'CDE25DA0']),
                        'hex16' => trans('plugins/license-manager::license-manager.general.hex16', ['hex16' => 'CDE25DA004D74201']),
                    ])
                    ->selected(setting('lm_product_unique_id_format', 'hex8'))
            )
            ->add(
                'lm_product_version_unique_id_format',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.product_version_unique_id_format'))
                    ->choices($formatUniqueIdChoices)
                    ->selected(setting('lm_product_version_unique_id_format', 'hex8'))
            )
            ->add(
                'lm_add_entries_for_failed_activation_attempts',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.add_entries_for_failed_activation_attempts'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.add_entries_for_failed_activation_attempts_helper'))
                    ->value(setting('lm_add_entries_for_failed_activation_attempts', false))
            )
            ->add(
                'lm_add_entries_for_failed_update_download_attempts',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.add_entries_for_failed_update_download_attempts'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.add_entries_for_failed_update_download_attempts_helper'))
                    ->value(setting('lm_add_entries_for_failed_update_download_attempts', false))
            )
            ->add(
                'lm_deactivate_old_activations_on_new_activation',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.deactivate_old_activations_on_new_activation'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.deactivate_old_activations_on_new_activation_helper'))
                    ->value(setting('lm_deactivate_old_activations_on_new_activation', false))
            )
            ->add(
                'lm_add_domain_of_first_activation_as_licensed_domain',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.add_domain_of_first_activation_as_licensed_domain'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.add_domain_of_first_activation_as_licensed_domain_helper'))
                    ->value(setting('lm_add_domain_of_first_activation_as_licensed_domain', false))
            )
            ->add(
                'lm_send_expiration_warnings',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.expiration_settings.send_expiration_warnings'))
                    ->helperText(trans('plugins/license-manager::license-manager.expiration_settings.send_expiration_warnings_helper'))
                    ->value(setting('lm_send_expiration_warnings', false))
            )
            ->add(
                'lm_expiration_warning_days',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.expiration_settings.expiration_warning_days'))
                    ->helperText(trans('plugins/license-manager::license-manager.expiration_settings.expiration_warning_days_helper'))
                    ->value(setting('lm_expiration_warning_days', '7,1'))
                    ->placeholder('7,1')
            )
            ->add(
                'lm_enable_public_verification',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.public_verify.enable_public_verification'))
                    ->helperText(trans('plugins/license-manager::license-manager.public_verify.enable_public_verification_helper'))
                    ->value(setting('lm_enable_public_verification', false))
            )
            ->add(
                'lm_customer_page_licenses',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.customer_page_licenses'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.customer_page_licenses_helper'))
                    ->value(setting('lm_customer_page_licenses', true))
            )
            ->add(
                'lm_customer_page_activations',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.customer_page_activations'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.customer_page_activations_helper'))
                    ->value(setting('lm_customer_page_activations', true))
            )
            ->add(
                'lm_allow_customer_deactivation',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.allow_customer_deactivation'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.allow_customer_deactivation_helper'))
                    ->value(setting('lm_allow_customer_deactivation', true))
            )
            ->add(
                'lm_get_more_licenses_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.general.get_more_licenses_url'))
                    ->helperText(trans('plugins/license-manager::license-manager.general.get_more_licenses_url_helper'))
                    ->value(setting('lm_get_more_licenses_url'))
                    ->placeholder('https://example.com/pricing')
            )
            ->add(
                'lm_enable_webhooks',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.webhook_settings.enable_webhooks'))
                    ->helperText(trans('plugins/license-manager::license-manager.webhook_settings.enable_webhooks_helper'))
                    ->value($webhooksEnabled = setting('lm_enable_webhooks', false))
            )
            ->addOpenCollapsible('lm_enable_webhooks', '1', $webhooksEnabled ? '1' : '0')
            ->add(
                'lm_webhook_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.webhook_settings.webhook_url'))
                    ->helperText(trans('plugins/license-manager::license-manager.webhook_settings.webhook_url_helper'))
                    ->value(setting('lm_webhook_url'))
                    ->placeholder('https://example.com/webhook')
            )
            ->add(
                'lm_webhook_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.webhook_settings.webhook_secret'))
                    ->helperText(trans('plugins/license-manager::license-manager.webhook_settings.webhook_secret_helper'))
                    ->value(setting('lm_webhook_secret'))
                    ->placeholder('your-webhook-secret')
            )
            ->addCloseCollapsible('lm_enable_webhooks', '1')
            ->add(
                'lm_enable_envato_integration',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.title'))
                    ->helperText(trans('plugins/license-manager::license-manager.envato_integration.description'))
                    ->value($envatoEnabled = setting('lm_enable_envato_integration', false))
            )
            ->addOpenCollapsible('lm_enable_envato_integration', '1', $envatoEnabled ? '1' : '0')
            ->add(
                'lm_envato_personal_token',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.form.envato_personal_token'))
                    ->helperText(trans('plugins/license-manager::license-manager.envato_integration.form.envato_personal_token_helper', [
                        'link' => '<a href="https://build.envato.com/create-token/" target="_blank">build.envato.com</a>',
                    ]))
                    ->value(setting('lm_envato_personal_token'))
                    ->placeholder('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
            )
            ->add(
                'lm_envato_owner_username',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.form.owner'))
                    ->value(setting('lm_envato_owner_username'))
                    ->placeholder('your-envato-username')
            )
            ->add(
                'lm_envato_marketplace',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.form.marketplace'))
                    ->choices(collect(EnvatoSite::cases())->mapWithKeys(fn (EnvatoSite $site) => [$site->value => $site->name])->all())
                    ->selected(setting('lm_envato_marketplace', EnvatoSite::CodeCanyon->value))
            )
            ->add(
                'lm_default_envato_license_uses_limit',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.form.default_envato_license_uses_limit'))
                    ->value(setting('lm_default_envato_license_uses_limit', 0))
                    ->placeholder('0')
            )
            ->add(
                'lm_default_envato_parallel_uses_limit',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.envato_integration.form.default_envato_parallel_uses_limit'))
                    ->value(setting('lm_default_envato_parallel_uses_limit'))
                    ->placeholder('0')
            )
            ->addCloseCollapsible('lm_enable_envato_integration', '1');
    }
}
