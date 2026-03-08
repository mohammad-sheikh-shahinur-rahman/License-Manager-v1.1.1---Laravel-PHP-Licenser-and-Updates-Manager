<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;

class ActivityLog extends BaseModel
{
    protected $table = 'lm_activity_logs';

    protected $fillable = [
        'type',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    public static function log(string $message, string $type = ''): static
    {
        return static::query()->create([
            'message' => $message,
            'type' => $type,
        ]);
    }
}
