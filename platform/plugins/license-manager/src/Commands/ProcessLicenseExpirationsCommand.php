<?php

namespace Botble\LicenseManager\Commands;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Services\WebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:license-manager:process-expirations', 'Process license expirations and send warning emails.')]
class ProcessLicenseExpirationsCommand extends Command
{
    public function __construct(protected WebhookService $webhookService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (setting('lm_send_expiration_warnings') != '1') {
            $this->info('Expiration warnings are disabled.');

            return self::SUCCESS;
        }

        $warningDays = $this->getWarningDays();
        $today = Carbon::today();

        $this->processExpiringLicenses($today, $warningDays);
        $this->processExpiredLicenses($today);
        $this->processExpiredUpdates($today);

        $this->components->info('License expiration processing complete.');

        return self::SUCCESS;
    }

    protected function getWarningDays(): array
    {
        $setting = setting('lm_expiration_warning_days', '7,1');

        return array_map('intval', array_filter(explode(',', $setting)));
    }

    protected function processExpiringLicenses(Carbon $today, array $warningDays): void
    {
        foreach ($warningDays as $days) {
            $targetDate = $today->copy()->addDays($days);

            $licenses = ProductLicense::query()
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', $targetDate)
                ->where('is_valid', true)
                ->whereNotNull('email')
                ->with('product')
                ->get();

            /** @var ProductLicense $license */
            foreach ($licenses as $license) {
                $this->sendExpirationWarning($license, $days);
            }

            if ($licenses->isNotEmpty()) {
                $this->info("Sent {$licenses->count()} warning emails for licenses expiring in {$days} days.");
            }
        }
    }

    protected function processExpiredLicenses(Carbon $today): void
    {
        $expiredLicenses = ProductLicense::query()
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', $today)
            ->where('is_valid', true)
            ->with('product')
            ->get();

        /** @var ProductLicense $license */
        foreach ($expiredLicenses as $license) {
            $this->logExpiredLicense($license);
        }

        if ($expiredLicenses->isNotEmpty()) {
            $this->info("Logged {$expiredLicenses->count()} expired licenses.");
        }
    }

    protected function processExpiredUpdates(Carbon $today): void
    {
        $expiredUpdates = ProductLicense::query()
            ->whereNotNull('updates_until')
            ->where('updates_until', '<', $today)
            ->where('is_valid', true)
            ->with('product')
            ->get();

        /** @var ProductLicense $license */
        foreach ($expiredUpdates as $license) {
            $this->logUpdateSupportExpired($license);
        }

        if ($expiredUpdates->isNotEmpty()) {
            $this->info("Logged {$expiredUpdates->count()} licenses with expired update support.");
        }
    }

    protected function sendExpirationWarning(ProductLicense $license, int $daysUntilExpiry): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown_product');

        // Send webhook notification
        $this->webhookService->dispatchLicenseExpirationWarning($license, $daysUntilExpiry);

        // Send email notification
        if (! $license->email) {
            return;
        }

        try {
            Mail::send(
                'plugins/license-manager::emails.license-expiring',
                [
                    'license' => $license,
                    'productName' => $productName,
                    'daysUntilExpiry' => $daysUntilExpiry,
                    'expiryDate' => $license->expires_at->format('Y-m-d'),
                ],
                function ($message) use ($license, $productName, $daysUntilExpiry): void {
                    $message->to($license->email)
                        ->subject(trans(
                            'plugins/license-manager::license-manager.emails.expiring_subject',
                            ['product_reference_id' => $productName, 'days' => $daysUntilExpiry]
                        ));
                }
            );

            ActivityLog::query()->create([
                'message' => trans('plugins/license-manager::license-manager.activity_log.expiration_warning_email_sent', [
                    'email' => e($license->email),
                    'license' => e($license->license_code),
                    'product' => e($productName),
                    'days' => $daysUntilExpiry,
                ]),
            ]);
        } catch (\Throwable $e) {
            $this->error("Failed to send email to {$license->email}: {$e->getMessage()}");
        }
    }

    protected function logExpiredLicense(ProductLicense $license): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        // Send webhook notification
        $this->webhookService->dispatchLicenseExpired($license);

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.license_expired', [
                'license' => e($license->license_code),
                'product' => e($productName),
                'date' => $license->expires_at->format('Y-m-d'),
            ]),
        ]);
    }

    protected function logUpdateSupportExpired(ProductLicense $license): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        // Send webhook notification
        $this->webhookService->dispatchUpdateSupportExpired($license);

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.license_updates_expired', [
                'license' => e($license->license_code),
                'product' => e($productName),
            ]),
        ]);
    }
}
