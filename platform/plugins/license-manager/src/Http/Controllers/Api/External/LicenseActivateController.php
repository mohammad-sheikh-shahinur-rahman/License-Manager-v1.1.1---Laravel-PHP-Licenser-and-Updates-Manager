<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Actions\LicenseCode\CreateLicenseCodeViaEnvato;
use Botble\LicenseManager\Envato;
use Botble\LicenseManager\Events\LicenseActivated;
use Botble\LicenseManager\Events\LicenseActivationFailed;
use Botble\LicenseManager\Http\Requests\Api\LicenseActivateRequest;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LicenseActivateController
{
    public function __invoke(
        LicenseActivateRequest $request,
        LicenseManager $licenseManager,
        Envato $envato
    ): JsonResponse {
        try {
            DB::beginTransaction();

            /** @var Product|null $product */
            $product = Product::query()->where('reference_id', $request->input('product_id'))->first();

            if (! $product) {
                return $this->errorResponse(trans('plugins/license-manager::license-manager.api.external.product_not_found'), 404);
            }

            $licenseCode = ProductLicense::query()
                ->whereRaw('LOWER(license_code) = ?', [Str::lower($request->input('license_code'))])
                ->first();

            if ($licenseCode === null && $request->input('verify_type') === 'envato') {
                $envatoResponse = $envato->verifyPurchaseCode($request->input('license_code'));

                if ($envatoResponse === false) {
                    return $this->responseInvalidLicense($product, $request, $licenseManager);
                }

                if (Arr::get($envatoResponse, 'item.id') != $product->envato_id) {
                    return $this->responseInvalidLicense($product, $request, $licenseManager);
                }

                if (strtolower($request->input('client_name')) !== strtolower(Arr::get($envatoResponse, 'buyer'))) {
                    return $this->responseInvalidLicense($product, $request, $licenseManager);
                }

                $licenseCode = (new CreateLicenseCodeViaEnvato())->handle(
                    $product,
                    $request->input('license_code'),
                    $envatoResponse
                );
            }

            if (
                $licenseCode === null
                || ($licenseCode && (
                    $licenseCode->product_reference_id !== $product->reference_id
                    || ($licenseCode->customer_id && strtolower($licenseCode->customer_id) !== strtolower($request->input('client_name')))
                ))
            ) {
                return $this->failedActivationResponse($product, $request, $licenseManager, trans('plugins/license-manager::license-manager.api.external.invalid_license_code'));
            }

            if (! $licenseCode->is_valid) {
                return $this->failedActivationResponse($product, $request, $licenseManager, trans('plugins/license-manager::license-manager.api.external.license_blocked'));
            }

            if ($licenseCode->expires_at && $licenseCode->expires_at->isPast()) {
                return $this->failedActivationResponse($product, $request, $licenseManager, trans('plugins/license-manager::license-manager.api.external.license_expired'));
            }

            $licenseActivationsCount = $licenseCode->activations()->where('is_active', true)->count();

            if ($licenseCode->parallel_uses !== null
                && $licenseActivationsCount >= $licenseCode->parallel_uses) {
                return $this->failedActivationResponse(
                    $product,
                    $request,
                    $licenseManager,
                    trans('plugins/license-manager::license-manager.api.external.max_parallel_uses_reached'),
                    'ACTIVATED_MAXIMUM_ALLOWED_PRODUCT_INSTANCES'
                );
            }

            $clientDomain = $licenseManager->getClientDomain();
            $domains = $licenseCode->domains ?? [];

            $domainAllowed = $licenseManager->isDomainAllowed($clientDomain, $domains);
            $domainAllowed = apply_filters('lm_is_domain_allowed', $domainAllowed, $clientDomain, $domains, $product, $licenseCode);

            // Check domain restriction with normalization (allows http/https, www/non-www variations)
            if (! $domainAllowed) {
                $formattedDomains = collect($domains)
                    ->map(fn ($domain) => $licenseManager->extractDomainFromUrl(trim($domain)) ?? $licenseManager->normalizeDomain(trim($domain)))
                    ->unique()
                    ->implode(', ');

                return $this->failedActivationResponse(
                    $product,
                    $request,
                    $licenseManager,
                    trans('plugins/license-manager::license-manager.validation.domain_not_allowed', ['domains' => $formattedDomains])
                );
            }

            $ips = $licenseCode->ips ?? [];
            $ipAllowed = empty($ips) || in_array($licenseManager->getClientIp(), $ips, true);
            $ipAllowed = apply_filters('lm_is_ip_allowed', $ipAllowed, $licenseManager->getClientIp(), $ips, $product, $licenseCode);

            if (! $ipAllowed) {
                return $this->failedActivationResponse($product, $request, $licenseManager, trans('plugins/license-manager::license-manager.api.external.ip_not_allowed'));
            }

            // Add normalized domain on first activation
            if ($licenseActivationsCount <= 0
                && setting('lm_add_domain_of_first_activation_as_licensed_domain') == '1') {
                $normalizedDomain = $licenseManager->getNormalizedClientDomain();

                if ($normalizedDomain) {
                    $domains[] = $normalizedDomain;
                    $licenseCode->domains = $domains;
                }
            }

            if (setting('lm_deactivate_old_activations_on_new_activation') == '1') {
                $licenseCode->activations()->where('is_active', true)->update(['is_active' => false]);
            }

            if (! $licenseCode->expires_at && $licenseCode->expiry_days > 0) {
                $licenseCode->expires_at = Date::now()->addDays($licenseCode->expiry_days)->startOfDay();
            }

            $licenseCode->save();

            $activation = new ProductActivation();
            $activation->product_reference_id = $product->reference_id;
            $activation->customer_id = $request->input('client_name');
            $activation->license_code = $request->input('license_code');
            $activation->url = $licenseManager->getClientUrl();
            $activation->ip_address = $licenseManager->getClientIp();
            $activation->activated_at = Date::now();
            $activation->user_agent = $request->userAgent() ?: '';
            $activation->is_valid = true;
            $activation->is_active = true;
            $activation->save();

            $encrypter = $licenseManager->createEncrypter();

            $licenseData = $encrypter->encrypt([
                'id' => $activation->getKey(),
                'activation_id' => $activation->id,
            ]);

            ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_activated_via_api', [
                'license' => e($request->input('license_code')),
                'product' => e($product->name),
                'domain' => e($licenseManager->getClientDomain() ?: $licenseManager->getClientUrl()),
                'ip' => e($licenseManager->getClientIp()),
            ]));

            DB::commit();

            do_action('lm_license_activated', $activation, $product, $licenseCode);
            LicenseActivated::dispatch($activation, $product, $licenseCode);

            $responseData = [
                'status' => true,
                'is_active' => true,
                'message' => trans('plugins/license-manager::license-manager.api.external.activated'),
                'lic_response' => $licenseData,
                'data' => [
                    'license_data' => $licenseData,
                ],
            ];

            $responseData = apply_filters('lm_api_activation_success_response', $responseData, $activation, $product, $licenseCode);

            return new JsonResponse($responseData, 200);
        } catch (Throwable $e) {
            DB::rollBack();

            report($e);

            return new JsonResponse([
                'status' => false,
                'is_active' => false,
                'message' => trans('plugins/license-manager::license-manager.api.external.activation_error'),
                'lic_response' => null,
                'data' => null,
            ], 500);
        }
    }

    protected function responseInvalidLicense(
        Product $product,
        LicenseActivateRequest $request,
        LicenseManager $licenseManager
    ): JsonResponse {
        $this->recordFailedActivation($product, $request, $licenseManager);

        DB::commit();

        $message = trans('plugins/license-manager::license-manager.api.external.invalid_license_code');

        do_action('lm_license_activation_failed', $product, $request, $message);
        LicenseActivationFailed::dispatch($product, $request, $message);

        $response = [
            'status' => false,
            'is_active' => false,
            'message' => $message,
            'lic_response' => null,
            'data' => null,
        ];

        $response = apply_filters('lm_api_activation_failed_response', $response, $product, $request);

        return new JsonResponse($response, 400);
    }

    protected function recordFailedActivation(
        Product $product,
        LicenseActivateRequest $request,
        LicenseManager $licenseManager
    ): void {
        if (setting('lm_add_entries_for_failed_activation_attempts') != '1') {
            return;
        }

        $activation = new ProductActivation();
        $activation->product_reference_id = $product->reference_id;
        $activation->customer_id = $request->input('client_name');
        $activation->license_code = $request->input('license_code');
        $activation->url = $licenseManager->getClientUrl();
        $activation->ip_address = $licenseManager->getClientIp();
        $activation->activated_at = Date::now();
        $activation->user_agent = $request->userAgent() ?: '';
        $activation->is_valid = false;
        $activation->is_active = false;
        $activation->save();
    }

    protected function failedActivationResponse(
        Product $product,
        LicenseActivateRequest $request,
        LicenseManager $licenseManager,
        string $message,
        ?string $statusCode = null
    ): JsonResponse {
        $this->recordFailedActivation($product, $request, $licenseManager);

        DB::commit();

        do_action('lm_license_activation_failed', $product, $request, $message);
        LicenseActivationFailed::dispatch($product, $request, $message);

        $response = [
            'status' => false,
            'is_active' => false,
            'message' => $message,
            'lic_response' => null,
            'data' => null,
        ];

        if ($statusCode) {
            $response['status_code'] = $statusCode;
        }

        $response = apply_filters('lm_api_activation_failed_response', $response, $product, $request);

        return new JsonResponse($response, 200);
    }

    protected function errorResponse(string $message, int $httpStatus = 400): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'is_active' => false,
            'message' => $message,
            'lic_response' => null,
            'data' => null,
        ], $httpStatus);
    }
}
