<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\ProductActivation;
use Illuminate\Http\Request;

class ActivatedProductActivationController extends BaseController
{
    use CompareClientId;

    public function destroy(ProductActivation $productActivation, Request $request)
    {
        abort_unless(setting('lm_customer_page_activations', true), 404);

        abort_unless(setting('lm_allow_customer_deactivation', true), 403);

        abort_unless($request->user() instanceof Customer, 403);

        abort_unless($this->compareClient(
            $request->user()->client_id,
            $productActivation->customer_id
        ), 403);

        $productActivation->update([
            'is_active' => false,
        ]);

        return BaseHttpResponse::make()
            ->setNextUrl(route('lm.activations.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
