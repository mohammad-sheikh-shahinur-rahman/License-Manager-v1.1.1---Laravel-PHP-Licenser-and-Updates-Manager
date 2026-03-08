<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Avatar;
use Botble\LicenseManager\Models\ProductLicense as ProductLicenseLB;
use Botble\LicenseManager\Notifications\CustomerResetPasswordNotification;
use Botble\LicenseManager\Tests\Factories\CustomerFactory;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContact;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class Customer extends BaseModel implements AuthenticatableContract, CanResetPasswordContact
{
    use Authenticatable;
    use CanResetPassword;
    use HasFactory;
    use Notifiable;

    protected $table = 'lm_customers';

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'client_id',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Customer $customer): void {
            $folder = Storage::path($customer->upload_folder);
            if (File::isDirectory($folder) && Str::endsWith($customer->upload_folder, '/' . $customer->getKey())) {
                File::deleteDirectory($folder);
            }
        });
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->avatar->url) {
                    return RvMedia::url($this->avatar->url);
                }

                try {
                    return (new Avatar())->create($this->name)->toBase64();
                } catch (Throwable) {
                    return RvMedia::getDefaultImage();
                }
            },
        );
    }

    protected function avatarThumbUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->avatar->url) {
                    return RvMedia::getImageUrl($this->avatar->url, 'thumb');
                }

                try {
                    return (new Avatar())->create($this->name)->toBase64();
                } catch (Throwable) {
                    return RvMedia::getDefaultImage();
                }
            },
        );
    }

    protected function uploadFolder(): Attribute
    {
        return Attribute::make(
            get: function ($_, array $attributes = []) {
                $folder = ! empty($attributes['id']) ? 'customers/' . $attributes['id'] : 'customers';

                return apply_filters('lm_customer_account_upload_folder', $folder, $this);
            }
        );
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class)->withDefault();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerResetPasswordNotification($token));
    }

    public function lbLicenses(): HasMany
    {
        return $this->hasMany(ProductLicenseLB::class, 'customer_id', 'client_id');
    }
}
