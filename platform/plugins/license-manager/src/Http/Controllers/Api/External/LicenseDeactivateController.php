<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Events\LicenseDeactivated;
use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithLicenses;
use Botble\LicenseManager\Http\Requests\Api\LicenseVerifyRequest;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Illuminate\Http\JsonResponse;

class LicenseDeactivateController
{
    use InteractsWithLicenses;

    public function __invoke(LicenseVerifyRequest $request, LicenseManager $licenseManager): JsonResponse
    {
        $product = Product::query()->where('reference_id', $request->input('product_id'))->first();

        if (! $product) {
            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.product_not_found'),
            ], 200);
        }

        // Support license_data (modern), license_file (legacy CMS), or license_code + client_name (revoke)
        $licenseData = $request->input('license_data') ?: $request->input('license_file');

        if ($licenseData) {
            $activation = $this->verifyLicense($product, $request->input('client_name'), $licenseData);
        } elseif ($request->filled('license_code')) {
            $activation = $this->findActivationByLicenseCode(
                $product,
                $request->input('license_code'),
                $request->input('client_name'),
                $licenseManager
            );
        } else {
            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.license_data_required'),
            ], 200);
        }

        if (! $activation) {
            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.invalid_license'),
            ], 200);
        }

        $activation->is_active = false;
        $activation->save();

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_deactivated_via_api', [
            'license' => e($activation->license_code),
            'product' => e($product->name),
            'domain' => e($activation->url),
        ]));

        do_action('lm_license_deactivated', $activation, $product);
        LicenseDeactivated::dispatch($activation, $product);

        $responseData = [
            'status' => true,
            'is_active' => true,
            'message' => trans('plugins/license-manager::license-manager.api.external.deactivated'),
        ];

        $responseData = apply_filters('lm_api_deactivation_response', $responseData, $activation, $product);

        return new JsonResponse($responseData, 200);
    }

    protected function findActivationByLicenseCode(
        Product $product,
        string $licenseCode,
        ?string $clientName,
        LicenseManager $licenseManager
    ): ProductActivation|false {
        $clientDomain = $licenseManager->getNormalizedClientDomain();

        $query = ProductActivation::query()
            ->whereRaw('LOWER(license_code) = ?', [strtolower($licenseCode)])
            ->where('product_reference_id', $product->reference_id)
            ->where('is_active', true)
            ->where('is_valid', true);

        if ($clientName) {
            $query->whereRaw('LOWER(customer_id) = ?', [strtolower($clientName)]);
        }

        $activations = $query->get();

        foreach ($activations as $activation) {
            $activationDomain = $licenseManager->extractDomainFromUrl($activation->url);

            if ($activationDomain === $clientDomain) {
                return $activation;
            }
        }

        return false;
    }
}
