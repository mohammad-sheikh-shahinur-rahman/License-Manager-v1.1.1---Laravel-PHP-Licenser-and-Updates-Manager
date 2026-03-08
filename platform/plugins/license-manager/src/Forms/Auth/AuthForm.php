<?php

namespace Botble\LicenseManager\Forms\Auth;

use Botble\Base\Forms\FormAbstract;
use Botble\Captcha\Facades\Captcha;
use Botble\LicenseManager\Models\Customer;

abstract class AuthForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Customer())
            ->withCustomFields()
            ->contentOnly();
    }

    public function addCaptchaFieldsWhenAvailable(): static
    {
        if (! is_plugin_active('captcha')) {
            return $this;
        }

        $this->when(Captcha::isEnabled(), function (self $form) {
            if (setting('lm_customer_recaptcha_enabled', false)) {
                $form
                    ->addCustomField('recaptcha', 'Botble\Captcha\Forms\Fields\ReCaptchaField')
                    ->add('recaptcha_field', 'recaptcha');
            }

            if (setting('lm_customer_math_captcha_enabled', false)) {
                $form
                    ->addCustomField('mathCaptcha', 'Botble\Captcha\Forms\Fields\MathCaptchaField')
                    ->add('math_captcha_field', 'mathCaptcha');
            }

            return $form;
        });

        return $this;
    }
}
