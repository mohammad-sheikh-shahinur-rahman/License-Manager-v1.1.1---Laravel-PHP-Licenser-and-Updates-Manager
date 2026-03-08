<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\LicenseManager\Rules\BlacklistedBuyerNameRule;
use Botble\LicenseManager\Rules\BlacklistedLicenseCodeRule;
use Botble\Support\Http\Requests\Request;

class LicenseActivateRequest extends Request
{
    public function rules(): array
    {
        return [
            'verify_type' => ['required', 'in:envato,non_envato'],
            'product_id' => ['required', 'string'],
            'license_code' => ['required', 'string', new BlacklistedLicenseCodeRule()],
            'client_name' => ['required', 'string', new BlacklistedBuyerNameRule()],
        ];
    }
}
