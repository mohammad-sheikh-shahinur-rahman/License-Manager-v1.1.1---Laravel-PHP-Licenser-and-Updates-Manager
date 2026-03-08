<?php

namespace Botble\LicenseManager\Notifications;

use Botble\Base\Facades\EmailHandler;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class CustomerResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetLink = route('lm.customer.auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $emailHandler = EmailHandler::setModule('license-manager')
            ->setTemplate('customer-password-reset')
            ->setType('plugins')
            ->addTemplateSettings('license-manager', config('plugins.license-manager.email', []))
            ->setVariableValues([
                'reset_link' => $resetLink,
                'customer_name' => $notifiable->name,
            ]);

        return (new MailMessage())
            ->view(['html' => new HtmlString($emailHandler->getContent())])
            ->subject($emailHandler->getSubject());
    }
}
