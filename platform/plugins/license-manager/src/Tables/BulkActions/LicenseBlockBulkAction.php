<?php

namespace Botble\LicenseManager\Tables\BulkActions;

use Botble\Base\Exceptions\DisabledInDemoModeException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\Table\Abstracts\TableBulkActionAbstract;
use Illuminate\Database\Eloquent\Model;

class LicenseBlockBulkAction extends TableBulkActionAbstract
{
    public function __construct()
    {
        $this
            ->label($label = trans('plugins/license-manager::license-manager.licenses.block'))
            ->confirmationModalButton($label)
            ->beforeDispatch(function (): void {
                throw_if(
                    BaseHelper::hasDemoModeEnabled(),
                    DisabledInDemoModeException::class
                );
            });
    }

    public function dispatch(Model|BaseModel $model, array $ids): BaseHttpResponse
    {
        $count = 0;

        $model
            ->newQuery()
            ->whereKey($ids)
            ->where('is_valid', true)
            ->each(function (BaseModel $item) use (&$count): void {
                $item->update(['is_valid' => false]);
                $count++;
            });

        if ($count) {
            ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.licenses_bulk_blocked', ['count' => $count]));
        }

        return (new BaseHttpResponse())
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
