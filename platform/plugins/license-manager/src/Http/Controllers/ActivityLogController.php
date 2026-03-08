<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Tables\ActivityLogTable;

class ActivityLogController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(
                trans('plugins/license-manager::license-manager.activity_log.title'),
                route('lm.activity-logs.index')
            );
    }

    public function index(ActivityLogTable $activityLogTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.activity_log.title'));

        return $activityLogTable->renderTable();
    }

    public function destroy(ActivityLog $activityLog)
    {
        return DeleteResourceAction::make($activityLog);
    }
}
