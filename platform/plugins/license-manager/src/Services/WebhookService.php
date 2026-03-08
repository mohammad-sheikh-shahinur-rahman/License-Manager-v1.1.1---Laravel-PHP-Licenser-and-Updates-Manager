<?php

namespace Botble\LicenseManager\Services;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookService
{
    protected const MAX_RETRIES = 3;

    protected const RETRY_DELAYS = [1000, 2000, 4000]; // Exponential backoff in milliseconds

    public function isEnabled(): bool
    {
        return setting('lm_enable_webhooks') == '1'
            && ! empty($this->getWebhookUrl());
    }

    public function getWebhookUrl(): ?string
    {
        return setting('lm_webhook_url');
    }

    public function getWebhookSecret(): ?string
    {
        return setting('lm_webhook_secret');
    }

    public function dispatch(string $eventType, array $payload): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $url = $this->getWebhookUrl();

        $payload = apply_filters('lm_webhook_payload', $payload, $eventType);

        $data = [
            'event_type' => $eventType,
            'timestamp' => Carbon::now()->toIso8601String(),
            'payload' => $payload,
        ];

        return $this->sendWithRetry($url, $data);
    }

    public function dispatchLicenseEvent(string $eventType, ProductLicense $license): bool
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        $payload = [
            'license_code' => $license->license_code,
            'product_reference_id' => $productName,
            'product_id' => $license->product_reference_id,
            'customer' => $license->customer_id,
            'email' => $license->email,
            'expires_at' => $license->expires_at?->toDateString(),
            'is_valid' => (bool) $license->is_valid,
        ];

        $success = $this->dispatch($eventType, $payload);

        $this->logWebhookAttempt($eventType, $license, $success);

        return $success;
    }

    public function dispatchLicenseExpirationWarning(ProductLicense $license, int $daysUntilExpiry): bool
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        $payload = [
            'license_code' => $license->license_code,
            'product_reference_id' => $productName,
            'product_id' => $license->product_reference_id,
            'customer' => $license->customer_id,
            'email' => $license->email,
            'expires_at' => $license->expires_at?->toDateString(),
            'days_until_expiry' => $daysUntilExpiry,
        ];

        $success = $this->dispatch('license.expiring', $payload);

        $this->logWebhookAttempt('license.expiring', $license, $success);

        return $success;
    }

    public function dispatchLicenseExpired(ProductLicense $license): bool
    {
        return $this->dispatchLicenseEvent('license.expired', $license);
    }

    public function dispatchUpdateSupportExpired(ProductLicense $license): bool
    {
        return $this->dispatchLicenseEvent('license.update_support_expired', $license);
    }

    protected function sendWithRetry(string $url, array $data): bool
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $data['event_type'],
        ];

        if ($secret = $this->getWebhookSecret()) {
            $headers['X-Webhook-Signature'] = $this->generateSignature($data, $secret);
        }

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(10)
                    ->post($url, $data);

                if ($response->successful()) {
                    return true;
                }

                Log::warning('Webhook failed with status ' . $response->is_active(), [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'response' => $response->body(),
                ]);
            } catch (RequestException $e) {
                Log::warning('Webhook request failed', [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                Log::error('Webhook unexpected error', [
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($attempt < self::MAX_RETRIES - 1) {
                usleep(self::RETRY_DELAYS[$attempt] * 1000);
            }
        }

        return false;
    }

    protected function generateSignature(array $data, string $secret): string
    {
        return hash_hmac('sha256', json_encode($data), $secret);
    }

    protected function logWebhookAttempt(string $eventType, ProductLicense $license, bool $success): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');
        $status = $success ? 'succeeded' : 'failed';

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.webhook_notification', [
                'event' => e($eventType),
                'status' => $status,
                'license' => e($license->license_code),
                'product' => e($productName),
            ]),
            'created_at' => Carbon::now(),
        ]);
    }
}
