<?php

namespace Botble\LicenseManager\Http\Requests\Customer;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordRequest extends Request
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }
}
