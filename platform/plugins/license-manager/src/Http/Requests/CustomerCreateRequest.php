<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomerCreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'min:2'],
            'email' => ['required', 'max:60', 'min:6', 'email', 'unique:lm_customers'],
            'client_id' => ['nullable', 'string', 'max:60', 'min:6', 'unique:lm_customers'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
