<?php

namespace Botble\LicenseManager\Http\Requests\Customer;

use Botble\Support\Http\Requests\Request;

class ProfileRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:120', 'min:2'],
        ];
    }
}
