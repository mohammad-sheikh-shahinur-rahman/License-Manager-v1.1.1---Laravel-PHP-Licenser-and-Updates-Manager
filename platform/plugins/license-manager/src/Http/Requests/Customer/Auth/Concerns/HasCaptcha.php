<?php

namespace Botble\LicenseManager\Http\Requests\Customer\Auth\Concerns;

use Botble\Captcha\Facades\Captcha;

trait HasCaptcha
{
    protected function determineIfCaptchaEnabled(): bool
    {
        if (! is_plugin_active('captcha')) {
            return false;
        }

        if (! Captcha::isEnabled()) {
            return false;
        }

        return true;
    }

    protected function mergeCaptchaRulesIfEnabled(array $rules): array
    {
        if (! $this->determineIfCaptchaEnabled()) {
            return $rules;
        }

        if (setting('lm_customer_recaptcha_enabled', false)) {
            $rules = [...$rules, ...Captcha::rules()];
        }

        if (setting('lm_customer_math_captcha_enabled', false)) {
            $rules = [...$rules, ...Captcha::mathCaptchaRules()];
        }

        return $rules;
    }

    protected function mergeCaptchaAttributeIfEnabled(array $attributes): array
    {
        if (! $this->determineIfCaptchaEnabled()) {
            return $attributes;
        }

        return [...$attributes, ...Captcha::attributes()];
    }
}
