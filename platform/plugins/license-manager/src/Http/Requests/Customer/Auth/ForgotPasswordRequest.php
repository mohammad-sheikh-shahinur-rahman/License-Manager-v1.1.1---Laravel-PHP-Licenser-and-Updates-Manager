<?php

namespace Botble\LicenseManager\Http\Requests\Customer\Auth;

use Botble\LicenseManager\Http\Requests\Customer\Auth\Concerns\HasCaptcha;
use Botble\Support\Http\Requests\Request;

class ForgotPasswordRequest extends Request
{
    use HasCaptcha;

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email', 'exists:lm_customers'],
        ];

        return $this->mergeCaptchaRulesIfEnabled($rules);
    }

    public function attributes(): array
    {
        return $this->mergeCaptchaAttributeIfEnabled([]);
    }
}
