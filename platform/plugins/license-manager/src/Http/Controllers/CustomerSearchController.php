<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Models\Customer;
use Illuminate\Http\Request;

class CustomerSearchController extends BaseController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string'],
        ]);

        return Customer::query()
            ->select(['client_id as id', 'name'])
            ->where('name', 'LIKE', '%' . $validated['q'] . '%')
            ->paginate(10);
    }
}
