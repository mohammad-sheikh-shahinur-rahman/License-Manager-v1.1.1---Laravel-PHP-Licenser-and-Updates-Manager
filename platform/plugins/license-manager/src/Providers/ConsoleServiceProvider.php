<?php

namespace Botble\LicenseManager\Providers;

use Botble\LicenseManager\Commands\ActivityLogClearCommand;
use Botble\LicenseManager\Commands\DownloadLogClearCommand;
use Botble\LicenseManager\Commands\GenerateLicensesCommand;
use Botble\LicenseManager\Commands\LBSettingSyncCommand;
use Botble\LicenseManager\Commands\ProcessAutoBlacklistCommand;
use Botble\LicenseManager\Commands\ProcessLicenseExpirationsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ActivityLogClearCommand::class,
            DownloadLogClearCommand::class,
            GenerateLicensesCommand::class,
            LBSettingSyncCommand::class,
            ProcessAutoBlacklistCommand::class,
            ProcessLicenseExpirationsCommand::class,
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule
                ->command(ActivityLogClearCommand::class)
                ->weekly();

            $schedule
                ->command(DownloadLogClearCommand::class)
                ->weekly();

            $schedule
                ->command(ProcessLicenseExpirationsCommand::class)
                ->dailyAt('02:00');

            $schedule
                ->command(ProcessAutoBlacklistCommand::class)
                ->hourly();
        });
    }
}
