<?php

use Botble\Setting\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        $theme = Setting::query()->where('key', 'theme')->value('value') ?: 'license';

        $oldKey = 'theme-' . $theme . '-website_copyright_text';
        $newKey = 'theme-' . $theme . '-copyright';

        $oldSetting = Setting::query()->where('key', $oldKey)->first();

        if (! $oldSetting) {
            return;
        }

        $existingNew = Setting::query()->where('key', $newKey)->first();

        if (! $existingNew) {
            Setting::query()->create([
                'key' => $newKey,
                'value' => $oldSetting->value,
            ]);
        }

        $oldSetting->delete();
    }
};
