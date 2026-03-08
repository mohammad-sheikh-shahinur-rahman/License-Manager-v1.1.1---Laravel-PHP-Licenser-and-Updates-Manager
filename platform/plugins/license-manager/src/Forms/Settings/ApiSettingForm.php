<?php

namespace Botble\LicenseManager\Forms\Settings;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TagFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TagField;
use Botble\LicenseManager\Http\Requests\Settings\ApiSettingRequest;
use Botble\Setting\Forms\SettingForm;
use Illuminate\Support\Arr;

class ApiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(ApiSettingRequest::class)
            ->setSectionTitle(PageTitle::getTitle(false))
            ->setSectionDescription(
                trans('plugins/license-manager::license-manager.api_setting.description')
            );

        $this
            ->add(
                'sample_app_download',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('plugins/license-manager::settings.partials.sample-app-download')
                    ->colspan(2)
                    ->toArray()
            )
            ->columns()
            ->add(
                'lm_blacklist_domain_after_failed_attempts',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.blacklist_domain_after_failed_attempts'))
                    ->placeholder('200')
                    ->helperText(trans('plugins/license-manager::license-manager.api_setting.blacklist_domain_after_failed_attempts_helper'))
                    ->value(old('lm_blacklist_domain_after_failed_attempts', setting('lm_blacklist_domain_after_failed_attempts', '200')))
            )
            ->add(
                'lm_blacklist_ip_after_failed_attempts',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.blacklist_ip_after_failed_attempts'))
                    ->placeholder('100')
                    ->helperText(trans('plugins/license-manager::license-manager.api_setting.blacklist_ip_after_failed_attempts_helper'))
                    ->value(old('lm_blacklist_ip_after_failed_attempts', setting('lm_blacklist_ip_after_failed_attempts', '100')))
            )
            ->add(
                'lm_requests_rate_limiting_method',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.requests_rate_limiting_method'))
                    ->choices([
                        'ip_address' => trans('plugins/license-manager::license-manager.api_setting.per_ip_address'),
                        'api_key' => trans('plugins/license-manager::license-manager.api_setting.per_api_key'),
                        // 'api_key_ip_address' => trans('plugins/license-manager::license-manager.api_setting.per_api_key_ip_address'),
                    ])
                    ->selected(setting('lm_requests_rate_limiting_method', 'api_key'))
            )
            ->add(
                'lm_requests_rate_limiting_period',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.requests_rate_limiting_period'))
                    ->placeholder('1000')
                    ->helperText(trans('plugins/license-manager::license-manager.api_setting.requests_rate_limiting_period_helper'))
                    ->value(old('lm_requests_rate_limiting_period', setting('lm_requests_rate_limiting_period')))
            )
            ->add(
                'lm_normalize_domain_variants',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.normalize_domain_variants'))
                    ->helperText(trans('plugins/license-manager::license-manager.api_setting.normalize_domain_variants_helper'))
                    ->value(old('lm_normalize_domain_variants', setting('lm_normalize_domain_variants', true)))
            )
            ->add(
                'lm_verify_license_ip',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.verify_license_ip'))
                    ->helperText(trans('plugins/license-manager::license-manager.api_setting.verify_license_ip_helper'))
                    ->value(old('lm_verify_license_ip', setting('lm_verify_license_ip', false)))
            )
            ->add(
                'lm_blacklists',
                AlertField::class,
                AlertFieldOption::make()
                    ->content(trans('plugins/license-manager::license-manager.api_setting.blacklists'))
                    ->colspan(2)
                    ->toArray()
            )
            ->add(
                'lm_blacklisted_domains',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.domain_blacklists'))
                    ->placeholder('example.com, *.example.org')
                    ->tap(function (TagFieldOption $tagFieldOption): void {
                        $values = old('lm_blacklisted_domains', setting('lm_blacklisted_domains')) ?: '[]';
                        $values = implode(',', Arr::flatten(json_decode($values, true)));

                        $tagFieldOption->value($values);
                    })
                    ->colspan(2)
                    ->toArray()
            )
            ->add(
                'lm_blacklisted_ips',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.ip_blacklists'))
                    ->placeholder('192.168.1.1, 10.0.0.*')
                    ->tap(function (TagFieldOption $tagFieldOption): void {
                        $values = old('lm_blacklisted_ips', setting('lm_blacklisted_ips')) ?: '[]';
                        $values = implode(',', Arr::flatten(json_decode($values, true)));

                        $tagFieldOption->value($values);
                    })
                    ->colspan(2)
                    ->toArray()
            )
            ->add(
                'lm_blacklisted_buyer_names',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.buyer_blacklists'))
                    ->placeholder('buyer_name, *spammer*')
                    ->tap(function (TagFieldOption $tagFieldOption): void {
                        $values = old('lm_blacklisted_buyer_names', setting('lm_blacklisted_buyer_names')) ?: '[]';
                        $values = $values ? implode(',', Arr::flatten(json_decode($values, true))) : '';

                        $tagFieldOption->value($values);
                    })
                    ->colspan(2)
                    ->toArray()
            )
            ->add(
                'lm_blacklisted_license_codes',
                TagField::class,
                TagFieldOption::make()
                    ->label(trans('plugins/license-manager::license-manager.api_setting.license_code_blacklists'))
                    ->placeholder('XXXX-XXXX-XXXX-XXXX')
                    ->tap(function (TagFieldOption $tagFieldOption): void {
                        $values = old('lm_blacklisted_license_codes', setting('lm_blacklisted_license_codes')) ?: [];
                        $values = $values ? implode(',', Arr::flatten(json_decode($values, true))) : '';

                        $tagFieldOption->value($values);
                    })
                    ->colspan(2)
                    ->toArray()
            )
            ->add(
                'api_keys_table',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('plugins/license-manager::settings.partials.api-keys-table')
                    ->colspan(2)
                    ->toArray()
            );
    }
}
