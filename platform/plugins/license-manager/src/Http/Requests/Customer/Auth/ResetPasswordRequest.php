<?php

namespace Botble\LicenseManager\Http\Requests\Customer\Auth;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordRequest extends Request
{
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:lm_customers'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }
}
