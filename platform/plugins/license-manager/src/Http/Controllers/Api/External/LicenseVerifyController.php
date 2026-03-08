<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Events\LicenseVerificationFailed;
use Botble\LicenseManager\Events\LicenseVerified;
use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithLicenses;
use Botble\LicenseManager\Http\Requests\Api\LicenseVerifyRequest;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\Product;
use Illuminate\Http\JsonResponse;

class LicenseVerifyController
{
    use InteractsWithLicenses;

    public function __invoke(LicenseVerifyRequest $request, LicenseManager $licenseManager): JsonResponse
    {
        /**
         * @var Product $product
         */
        $product = Product::query()->where('reference_id', $request->input('product_id'))->first();

        if (! $product) {
            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.product_not_found'),
                'data' => null,
            ], 200);
        }

        // Support both license_data (modern) and license_file (legacy CMS)
        $licenseData = $request->input('license_data') ?: $request->input('license_file');
        $clientName = $request->input('client_name', '');

        $activation = $this->verifyLicense($product, $clientName, $licenseData);

        if (! $activation) {
            do_action('lm_license_verification_failed', $product, $request);
            LicenseVerificationFailed::dispatch($product, $request);

            $responseData = [
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.invalid_license'),
                'data' => null,
            ];

            $responseData = apply_filters('lm_api_verification_response', $responseData, null, $product);

            return new JsonResponse($responseData, 200);
        }

        do_action('lm_license_verified', $activation, $product);
        LicenseVerified::dispatch($activation, $product);

        $responseData = [
            'status' => true,
            'is_active' => true,
            'message' => trans('plugins/license-manager::license-manager.api.external.verified'),
            'data' => null,
        ];

        $responseData = apply_filters('lm_api_verification_response', $responseData, $activation, $product);

        return new JsonResponse($responseData, 200);
    }
}
