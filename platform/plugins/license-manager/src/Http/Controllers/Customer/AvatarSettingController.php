<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Http\Requests\Customer\AvatarRequest;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AvatarSettingController extends BaseController
{
    public function __invoke(AvatarRequest $request, BaseHttpResponse $response)
    {
        try {
            $customer = Auth::user();

            $result = RvMedia::uploadFromBlob($request->file('avatar_file'), folderSlug: $customer->upload_folder);

            if ($result['error']) {
                return $response->setError()->setMessage($result['message']);
            }

            $file = $result['data'];

            $mediaFile = MediaFile::query()->find($customer->avatar_id);
            $mediaFile?->forceDelete();
            $customer->avatar_id = $file->id;
            $customer->save();

            return $response
                ->setMessage(trans('plugins/license-manager::customer.avatar_form.success'))
                ->setData(['url' => RvMedia::url($file->url)]);
        } catch (Throwable $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
