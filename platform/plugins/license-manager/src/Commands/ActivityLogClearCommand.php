<?php

namespace Botble\LicenseManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:license-manager:activity-log:clear', 'Clears license activity logs.')]
class ActivityLogClearCommand extends Command
{
    public function handle(): int
    {
        $count = DB::table('lm_activity_logs')->count();

        DB::table('lm_activity_logs')->truncate();

        $this->components->info(sprintf('Delete %s logs successfully', $count));

        return self::SUCCESS;
    }
}
