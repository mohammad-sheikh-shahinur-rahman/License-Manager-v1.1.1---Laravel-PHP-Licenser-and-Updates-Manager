<?php

namespace Botble\LicenseManager\Tables\BulkActions;

use Botble\Base\Contracts\BaseModel;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\Table\BulkActions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;

class LicenseDeleteBulkAction extends DeleteBulkAction
{
    public function dispatch(BaseModel|Model $model, array $ids): BaseHttpResponse
    {
        $count = 0;

        $model->newQuery()->whereKey($ids)->each(function (BaseModel|Model $item) use (&$count): void {
            $item->delete();

            DeletedContentEvent::dispatch($item::class, request(), $item);

            $count++;
        });

        if ($count) {
            ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.licenses_bulk_deleted', ['count' => $count]));
        }

        return BaseHttpResponse::make()
            ->withDeletedSuccessMessage();
    }
}
