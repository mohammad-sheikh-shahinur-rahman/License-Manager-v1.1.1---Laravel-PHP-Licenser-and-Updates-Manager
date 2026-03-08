<?php

return [
    [
        'name' => 'License Manager',
        'flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Customers',
        'flag' => 'lm.customers.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Create Customer',
        'flag' => 'lm.customers.create',
        'parent_flag' => 'lm.customers.index',
    ],
    [
        'name' => 'Edit Customer',
        'flag' => 'lm.customers.edit',
        'parent_flag' => 'lm.customers.index',
    ],
    [
        'name' => 'Delete Customer',
        'flag' => 'lm.customers.destroy',
        'parent_flag' => 'lm.customers.index',
    ],
    [
        'name' => 'Product Licenses',
        'flag' => 'lm.licenses.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Create License',
        'flag' => 'lm.licenses.create',
        'parent_flag' => 'lm.licenses.index',
    ],
    [
        'name' => 'Edit License',
        'flag' => 'lm.licenses.edit',
        'parent_flag' => 'lm.licenses.index',
    ],
    [
        'name' => 'Delete License',
        'flag' => 'lm.licenses.destroy',
        'parent_flag' => 'lm.licenses.index',
    ],
    [
        'name' => 'Export Licenses',
        'flag' => 'lm.licenses.export',
        'parent_flag' => 'lm.licenses.index',
    ],
    [
        'name' => 'Import Licenses',
        'flag' => 'lm.licenses.import',
        'parent_flag' => 'lm.licenses.index',
    ],
    [
        'name' => 'Product Activations',
        'flag' => 'lm.activations.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Delete Product Activation',
        'flag' => 'lm.activations.destroy',
        'parent_flag' => 'lm.activations.index',
    ],
    [
        'name' => 'Edit Product Activation',
        'flag' => 'lm.activations.edit',
        'parent_flag' => 'lm.activations.index',
    ],
    [
        'name' => 'Products',
        'flag' => 'lm.products.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Create Product',
        'flag' => 'lm.products.create',
        'parent_flag' => 'lm.products.index',
    ],
    [
        'name' => 'Edit Product',
        'flag' => 'lm.products.edit',
        'parent_flag' => 'lm.products.index',
    ],
    [
        'name' => 'View Product',
        'flag' => 'lm.products.show',
        'parent_flag' => 'lm.products.index',
    ],
    [
        'name' => 'Product Versions',
        'flag' => 'lm.products.versions.index',
        'parent_flag' => 'lm.products.index',
    ],
    [
        'name' => 'Create Product Version',
        'flag' => 'lm.products.versions.create',
        'parent_flag' => 'lm.products.versions.index',
    ],
    [
        'name' => 'Edit Product Version',
        'flag' => 'lm.products.versions.edit',
        'parent_flag' => 'lm.products.versions.index',
    ],
    [
        'name' => 'Delete Product Version',
        'flag' => 'lm.products.versions.destroy',
        'parent_flag' => 'lm.products.versions.index',
    ],
    [
        'name' => 'Delete Product',
        'flag' => 'lm.products.destroy',
        'parent_flag' => 'lm.products.index',
    ],
    [
        'name' => 'Update Downloads',
        'flag' => 'lm.update-downloads.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Activity Logs',
        'flag' => 'lm.activity-logs.index',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'PHP Obfuscator',
        'flag' => 'lm.php-obfuscator',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Generate Helper',
        'flag' => 'lm.generate-helper',
        'parent_flag' => 'packages.license-manager',
    ],
    [
        'name' => 'Manual Cron',
        'flag' => 'lm.manual-cron',
        'parent_flag' => 'packages.license-manager',
    ],

    [
        'name' => 'License Manager Settings',
        'flag' => 'lm.settings.index',
        'parent_flag' => 'settings.index',
    ],
    [
        'name' => 'General Settings',
        'flag' => 'lm.settings.general',
        'parent_flag' => 'settings.index',
    ],
    [
        'name' => 'Website Appearance Settings',
        'flag' => 'lm.settings.website_appearance',
        'parent_flag' => 'lm.settings.index',
    ],
    [
        'name' => 'Manage API Keys',
        'flag' => 'lm.api_keys.settings',
        'parent_flag' => 'lm.settings.index',
    ],
    [
        'name' => 'API Settings',
        'flag' => 'lm.api.settings',
        'parent_flag' => 'lm.settings.index',
    ],
];
