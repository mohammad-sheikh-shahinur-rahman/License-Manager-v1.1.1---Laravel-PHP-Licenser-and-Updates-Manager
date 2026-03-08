<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;

class CustomerActivityLog extends BaseModel
{
    protected $table = 'lm_customer_activity_logs';

    protected $fillable = [
        'customer_id',
        'type',
        'message',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'client_id');
    }
}
