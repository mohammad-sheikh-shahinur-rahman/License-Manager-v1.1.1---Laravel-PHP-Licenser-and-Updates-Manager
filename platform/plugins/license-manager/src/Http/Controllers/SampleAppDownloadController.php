<?php

namespace Botble\LicenseManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class SampleAppDownloadController extends LicenseManagerController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $type = $request->get('type', 'external');
        $fileName = $type === 'internal' ? 'sample-app-internal.php' : 'sample-app.php';
        $downloadName = $type === 'internal' ? 'license-manager-internal-sample-app.zip' : 'license-manager-sample-app.zip';

        $sampleAppFile = plugin_path('license-manager/resources/sample-app/' . $fileName);

        if (! File::exists($sampleAppFile)) {
            abort(404, trans('plugins/license-manager::license-manager.tools.sample_app_file_not_found'));
        }

        $tempFile = storage_path('app/temp/sample-app-' . $type . '-' . time() . '.zip');

        File::ensureDirectoryExists(dirname($tempFile));

        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, trans('plugins/license-manager::license-manager.tools.sample_app_zip_error'));
        }

        $zip->addFile($sampleAppFile, $fileName);
        $zip->close();

        return response()
            ->download($tempFile, $downloadName, [
                'Content-Type' => 'application/zip',
            ])
            ->deleteFileAfterSend();
    }
}
