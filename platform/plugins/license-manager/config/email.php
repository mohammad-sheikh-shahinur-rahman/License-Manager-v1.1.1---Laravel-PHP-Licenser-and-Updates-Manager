<?php

return [
    'name' => 'plugins/license-manager::license-manager.settings.email.title',
    'description' => 'plugins/license-manager::license-manager.settings.email.description',
    'templates' => [
        'customer-password-reset' => [
            'title' => 'plugins/license-manager::license-manager.settings.email.templates.customer_password_reset.title',
            'description' => 'plugins/license-manager::license-manager.settings.email.templates.customer_password_reset.description',
            'subject' => 'plugins/license-manager::license-manager.settings.email.templates.customer_password_reset.subject',
            'can_off' => false,
            'variables' => [
                'reset_link' => 'plugins/license-manager::license-manager.settings.email.templates.customer_password_reset.reset_link',
                'customer_name' => 'plugins/license-manager::license-manager.settings.email.templates.customer_password_reset.customer_name',
            ],
        ],
        'customer-welcome' => [
            'title' => 'plugins/license-manager::license-manager.settings.email.templates.customer_welcome.title',
            'description' => 'plugins/license-manager::license-manager.settings.email.templates.customer_welcome.description',
            'subject' => 'plugins/license-manager::license-manager.settings.email.templates.customer_welcome.subject',
            'can_off' => true,
            'variables' => [
                'reset_link' => 'plugins/license-manager::license-manager.settings.email.templates.customer_welcome.reset_link',
                'customer_name' => 'plugins/license-manager::license-manager.settings.email.templates.customer_welcome.customer_name',
            ],
        ],
    ],
];
