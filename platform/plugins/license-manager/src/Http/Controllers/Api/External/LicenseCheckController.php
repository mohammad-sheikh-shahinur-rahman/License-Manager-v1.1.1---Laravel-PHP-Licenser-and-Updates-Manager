<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Envato;
use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InvalidResponse;
use Botble\LicenseManager\Http\Requests\Api\LicenseCheckRequest;
use Illuminate\Http\JsonResponse;

class LicenseCheckController
{
    use InvalidResponse;

    public function __invoke(LicenseCheckRequest $request, Envato $envato): JsonResponse
    {
        $data = $envato->verifyPurchaseCode($request->input('purchase_code'));

        if (! $data) {
            return $this->responseInvalidLicense();
        }

        $responseData = [
            'message' => trans('plugins/license-manager::license-manager.api.external.verified'),
            'data' => $data,
        ];

        $responseData = apply_filters('lm_api_license_check_response', $responseData, $data);

        return response()->json($responseData);
    }
}
