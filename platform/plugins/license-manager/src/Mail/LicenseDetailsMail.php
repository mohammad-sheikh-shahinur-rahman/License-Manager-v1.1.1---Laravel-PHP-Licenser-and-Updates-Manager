<?php

namespace Botble\LicenseManager\Mail;

use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseDetailsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ProductLicense $license,
        public string $productName
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('plugins/license-manager::license-manager.emails.license_detailm_subject', [
                'product_reference_id' => $this->productName,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'plugins/license-manager::emails.license-details',
            with: [
                'license' => $this->license,
                'productName' => $this->productName,
            ],
        );
    }
}
