<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Models\UpdateDownload;
use Botble\LicenseManager\Tables\UpdateDownloadTable;

class UpdateDownloadController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.update_downloads.title'), route('lm.update-downloads.index'));
    }

    public function index(UpdateDownloadTable $updateDownloadTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.update_downloads.title'));

        return $updateDownloadTable->renderTable();
    }

    public function destroy(UpdateDownload $updateDownload)
    {
        return DeleteResourceAction::make($updateDownload);
    }
}
