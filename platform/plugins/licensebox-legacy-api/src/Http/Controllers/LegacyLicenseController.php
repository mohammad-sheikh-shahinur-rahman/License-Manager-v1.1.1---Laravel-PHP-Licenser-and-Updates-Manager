<?php

namespace Botble\LegacyApi\Http\Controllers;

use Botble\LegacyApi\Http\Controllers\Concerns\LegacyResponse;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyLicenseController
{
    use LegacyResponse;

    public function createLicense(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');

        if (empty($productId)) {
            return $this->missingValuesResponse();
        }

        $product = $this->findProductByLegacyId($productId);

        if (! $product) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_incorrect'));
        }

        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            $licenseCode = $this->generateLicenseCode();
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $licenseCode)) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_invalid'), 400);
        }

        if (ProductLicense::query()->where('license_code', $licenseCode)->exists()) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_exists'));
        }

        $clientEmail = $request->input('client_email');

        if (! empty($clientEmail) && ! filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.client_email_invalid'));
        }

        $uses = $request->input('license_uses');
        $parallelUses = $request->input('license_parallel_uses');

        $license = ProductLicense::query()->create([
            'product_reference_id' => $productId,
            'license_code' => $licenseCode,
            'type' => $request->input('license_type'),
            'invoice' => $request->input('invoice_number'),
            'customer_id' => $request->input('client_name'),
            'email' => $clientEmail,
            'comments' => $request->input('comments'),
            'ips' => $request->input('licensed_ips'),
            'domains' => $request->input('licensed_domains'),
            'support_until' => $request->input('support_end_date'),
            'updates_until' => $request->input('updates_end_date'),
            'expires_at' => $request->input('expiry_date'),
            'expiry_days' => $request->input('expiry_days'),
            'uses' => $uses,
            'parallel_uses' => $parallelUses,
            'is_valid' => 1,
        ]);

        if ($license) {
            ActivityLog::query()->create([
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.license_added', ['product' => e($product->name), 'code' => e($licenseCode)]),
                'created_at' => Carbon::now(),
            ]);

            return $this->legacySuccess(
                trans('plugins/licensebox-legacy-api::legacy-api.success.license_added', ['product' => $product->name, 'code' => $licenseCode]),
                ['license_code' => $licenseCode]
            );
        }

        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_not_added'));
    }

    public function editLicense(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            return $this->missingValuesResponse();
        }

        $license = $this->findLicenseByCode($licenseCode);

        if (! $license) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_not_exist'));
        }

        $productId = $request->input('product_id', $license->product_reference_id);

        if (! empty($request->input('product_id'))) {
            $product = $this->findProductByLegacyId($productId);

            if (! $product) {
                return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.product_id_incorrect'));
            }
        }

        $clientEmail = $this->resolveField($request, 'client_email', $license->email);

        if (! empty($clientEmail) && ! filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.client_email_invalid'));
        }

        $data = [
            'product_reference_id' => $productId,
            'type' => $this->resolveField($request, 'license_type', $license->type),
            'invoice' => $this->resolveField($request, 'invoice_number', $license->invoice),
            'customer_id' => $this->resolveField($request, 'client_name', $license->customer_id),
            'email' => $clientEmail,
            'comments' => $this->resolveField($request, 'comments', $license->comments),
            'ips' => $this->resolveField($request, 'licensed_ips', $license->ips),
            'domains' => $this->resolveField($request, 'licensed_domains', $license->domains),
            'support_until' => $this->resolveField($request, 'support_end_date', $license->support_until),
            'updates_until' => $this->resolveField($request, 'updates_end_date', $license->updates_until),
            'expires_at' => $this->resolveField($request, 'expiry_date', $license->expires_at),
            'expiry_days' => $this->resolveField($request, 'expiry_days', $license->expiry_days),
            'uses' => $this->resolveField($request, 'license_uses', $license->uses),
            'parallel_uses' => $this->resolveField($request, 'license_parallel_uses', $license->parallel_uses),
        ];

        if ($license->update($data)) {
            ActivityLog::query()->create([
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.license_edited', ['code' => e($licenseCode)]),
                'created_at' => Carbon::now(),
            ]);

            return $this->legacySuccess(
                trans('plugins/licensebox-legacy-api::legacy-api.success.license_edited', ['code' => $licenseCode]),
                ['license_code' => $licenseCode]
            );
        }

        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_not_edited'));
    }

    public function getLicense(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            return $this->missingValuesResponse();
        }

        $license = $this->findLicenseByCode($licenseCode);

        if (! $license) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_incorrect'));
        }

        $product = Product::query()->where('reference_id', $license->product_reference_id)->first();

        $currentActivations = ProductActivation::query()
            ->where('license_code', $licenseCode)
            ->count();

        $currentActiveActivations = ProductActivation::query()
            ->where('license_code', $licenseCode)
            ->where('is_active', 1)
            ->count();

        $licensesLeft = null;

        if ($license->uses !== null) {
            $licensesLeft = max(0, $license->uses - $currentActivations);
        }

        $parallelLicensesLeft = null;

        if ($license->parallel_uses !== null) {
            $parallelLicensesLeft = max(0, $license->parallel_uses - $currentActiveActivations);
        }

        $isValid = $this->checkLicenseValidity($license, $licensesLeft, $parallelLicensesLeft);

        return new JsonResponse([
            'status' => true,
            'license_code' => $license->license_code,
            'product_id' => $license->product_reference_id,
            'product_name' => $product?->name,
            'license_type' => $license->type,
            'client_name' => $license->customer_id,
            'client_email' => $license->email,
            'invoice_number' => $license->invoice,
            'license_comments' => $license->comments,
            'licensed_ips' => $license->ips,
            'licensed_domains' => $license->domains,
            'uses' => $license->uses,
            'uses_left' => $licensesLeft,
            'parallel_uses' => $license->parallel_uses,
            'parallel_uses_left' => $parallelLicensesLeft,
            'license_expiry' => $license->expires_at?->format('Y-m-d H:i:s'),
            'support_expiry' => $license->support_until?->format('Y-m-d H:i:s'),
            'updates_expiry' => $license->updates_until?->format('Y-m-d H:i:s'),
            'date_modified' => $license->created_at?->format('Y-m-d H:i:s'),
            'is_blocked' => ! $license->is_valid,
            'is_a_envato_purchase_code' => (bool) $license->is_envato,
            'is_valid_for_future_activations' => $isValid,
        ]);
    }

    public function searchLicense(Request $request): JsonResponse
    {
        $keyword = $request->input('keyword');

        if (empty($keyword)) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.no_search_keyword'), 400);
        }

        $escapedKeyword = str_replace(['%', '_'], ['\\%', '\\_'], $keyword);

        $licenses = ProductLicense::query()
            ->select('product_reference_id as product_id', 'license_code', 'type as license_type', 'customer_id as client_name', 'email as client_email')
            ->where('license_code', 'LIKE', "%{$escapedKeyword}%")
            ->limit(10)
            ->get()
            ->toArray();

        if (! empty($licenses)) {
            return new JsonResponse([
                'status' => true,
                'results_count' => count($licenses),
                'results' => $licenses,
            ]);
        }

        return new JsonResponse([
            'status' => true,
            'results' => trans('plugins/licensebox-legacy-api::legacy-api.errors.no_license_found'),
        ]);
    }

    public function deleteLicense(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            return $this->missingValuesResponse();
        }

        $license = $this->findLicenseByCode($licenseCode);

        if (! $license) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_incorrect'));
        }

        ProductActivation::query()->where('license_code', $licenseCode)->delete();

        if ($license->delete()) {
            ActivityLog::query()->create([
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.license_deleted', ['code' => e($licenseCode)]),
                'created_at' => Carbon::now(),
            ]);

            return $this->legacySuccess(trans('plugins/licensebox-legacy-api::legacy-api.success.license_deleted', ['code' => $licenseCode]));
        }

        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_not_deleted', ['code' => $licenseCode]));
    }

    public function blockLicense(Request $request): JsonResponse
    {
        return $this->toggleLicenseValidity($request, 0, 'blocked');
    }

    public function unblockLicense(Request $request): JsonResponse
    {
        return $this->toggleLicenseValidity($request, 1, 'unblocked');
    }

    public function deactivateActivations(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            return $this->missingValuesResponse();
        }

        $license = $this->findLicenseByCode($licenseCode);

        if (! $license) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_incorrect'));
        }

        $affected = ProductActivation::query()
            ->where('license_code', $licenseCode)
            ->where('is_active', 1)
            ->update(['is_active' => 0]);

        if ($affected > 0) {
            ActivityLog::query()->create([
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.activations_deactivated', ['code' => e($licenseCode)]),
                'created_at' => Carbon::now(),
            ]);

            return $this->legacySuccess(trans('plugins/licensebox-legacy-api::legacy-api.success.activations_deactivated', ['code' => $licenseCode]));
        }

        return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_already_status', ['code' => $licenseCode, 'status' => 'deactivated']));
    }

    /**
     * Resolve a field value from the request following LicenseBox convention:
     * - If field is present and non-empty: use request value
     * - If field is present but empty string: set to null
     * - If field is not present: keep existing value
     */
    protected function resolveField(Request $request, string $field, mixed $default): mixed
    {
        if (! $request->has($field)) {
            return $default;
        }

        $value = $request->input($field);

        return $value !== '' ? $value : null;
    }

    protected function toggleLicenseValidity(Request $request, int $validity, string $label): JsonResponse
    {
        $licenseCode = $request->input('license_code');

        if (empty($licenseCode)) {
            return $this->missingValuesResponse();
        }

        $license = $this->findLicenseByCode($licenseCode);

        if (! $license) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_code_incorrect'));
        }

        if ((int) $license->is_valid === $validity) {
            return $this->legacyError(trans('plugins/licensebox-legacy-api::legacy-api.errors.license_already_status', ['code' => $licenseCode, 'status' => $label]));
        }

        $license->update(['is_valid' => $validity]);

        ActivityLog::query()->create([
            'message' => trans('plugins/licensebox-legacy-api::legacy-api.activity_log.license_status_changed', ['code' => e($licenseCode), 'status' => $label]),
            'created_at' => Carbon::now(),
        ]);

        return $this->legacySuccess(trans('plugins/licensebox-legacy-api::legacy-api.success.license_status_changed', ['code' => $licenseCode, 'status' => $label]));
    }

    protected function checkLicenseValidity(
        ProductLicense $license,
        ?int $licensesLeft,
        ?int $parallelLicensesLeft
    ): bool {
        if (! $license->is_valid) {
            return false;
        }

        if ($licensesLeft !== null && $licensesLeft <= 0) {
            return false;
        }

        if ($parallelLicensesLeft !== null && $parallelLicensesLeft <= 0) {
            return false;
        }

        if ($license->expires_at && Carbon::now()->greaterThan($license->expires_at)) {
            return false;
        }

        if ($license->expiry_days) {
            $oldestActivation = ProductActivation::query()
                ->where('license_code', $license->license_code)
                ->oldest('activated_at')
                ->first();

            if ($oldestActivation && Carbon::now()->diffInDays($oldestActivation->activated_at) >= $license->expiry_days) {
                return false;
            }
        }

        return true;
    }

    protected function generateLicenseCode(int $attempts = 0): string
    {
        if ($attempts >= 10) {
            // Fallback to UUID-based code after too many collisions
            return Str::upper(Str::random(16));
        }

        $format = setting('lm_license_code_format', 'XXXX-XXXX-XXXX-XXXX');

        $code = preg_replace_callback('/X/', function () {
            return Str::upper(Str::random(1));
        }, $format);

        if (ProductLicense::query()->where('license_code', $code)->exists()) {
            return $this->generateLicenseCode($attempts + 1);
        }

        return $code;
    }
}
