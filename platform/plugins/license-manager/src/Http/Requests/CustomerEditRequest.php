<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomerEditRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'max:120', 'min:2'],
            'client_id' => ['nullable', 'max:60', 'min:6', 'unique:lm_customers,client_id,' . $this->route('customer.id')],
            'email' => ['required', 'max:60', 'min:6', 'email', 'unique:lm_customers,email,' . $this->route('customer.id')],
        ];

        if ($this->boolean('is_change_password')) {
            $rules['password'] = ['required', 'string', 'min:6'];
            $rules['password_confirmation'] = ['required', 'same:password'];
        }

        return $rules;
    }
}
