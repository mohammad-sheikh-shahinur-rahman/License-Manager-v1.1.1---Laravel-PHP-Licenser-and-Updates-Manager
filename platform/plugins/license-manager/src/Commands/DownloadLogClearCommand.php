<?php

namespace Botble\LicenseManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:license-manager:download-log:clear', 'Clears update download logs.')]
class DownloadLogClearCommand extends Command
{
    public function handle(): int
    {
        $count = DB::table('lm_update_downloads')->count();

        DB::table('lm_update_downloads')->truncate();

        $this->components->info(sprintf('Delete %s logs successfully', $count));

        return self::SUCCESS;
    }
}
