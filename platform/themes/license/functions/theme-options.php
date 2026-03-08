<?php

app()->booted(function (): void {
    theme_option()
        ->setSection([
            'title' => __('General'),
            'id' => 'opt-text-subsection-general',
            'subsection' => true,
            'icon' => 'ti ti-home',
        ])
        ->setSection([
            'title' => __('Logo & Favicon'),
            'id' => 'opt-text-subsection-logo',
            'subsection' => true,
            'icon' => 'ti ti-photo',
        ])
        ->setSection([
            'title' => __('Colors'),
            'id' => 'opt-text-subsection-colors',
            'subsection' => true,
            'icon' => 'ti ti-palette',
        ])
        ->setSection([
            'title' => __('Typography'),
            'id' => 'opt-text-subsection-typography',
            'subsection' => true,
            'icon' => 'ti ti-typography',
        ])
        ->setField([
            'id' => 'site_title',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'text',
            'label' => __('Site title'),
            'attributes' => [
                'name' => 'site_title',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'data-counter' => 120,
                ],
            ],
        ])
        ->setField([
            'id' => 'logo',
            'section_id' => 'opt-text-subsection-logo',
            'type' => 'mediaImage',
            'label' => __('Logo'),
            'attributes' => [
                'name' => 'logo',
                'value' => null,
            ],
        ])
        ->setField([
            'id' => 'favicon',
            'section_id' => 'opt-text-subsection-logo',
            'type' => 'mediaImage',
            'label' => __('Favicon'),
            'attributes' => [
                'name' => 'favicon',
                'value' => null,
            ],
        ])
        ->setField([
            'id' => 'login_screen_backgrounds',
            'section_id' => 'opt-text-subsection-logo',
            'type' => 'mediaImages',
            'label' => __('Login screen backgrounds'),
            'attributes' => [
                'name' => 'login_screen_backgrounds',
                'value' => null,
            ],
        ])
        ->setField([
            'id' => 'primary_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Primary color'),
            'attributes' => [
                'name' => 'primary_color',
                'value' => '#206bc4',
            ],
        ])
        ->setField([
            'id' => 'secondary_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Secondary color'),
            'attributes' => [
                'name' => 'secondary_color',
                'value' => '#6c7a91',
            ],
        ])
        ->setField([
            'id' => 'heading_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Heading color'),
            'attributes' => [
                'name' => 'heading_color',
                'value' => 'inherit',
            ],
        ])
        ->setField([
            'id' => 'text_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Text color'),
            'attributes' => [
                'name' => 'text_color',
                'value' => '#182433',
            ],
        ])
        ->setField([
            'id' => 'link_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Link color'),
            'attributes' => [
                'name' => 'link_color',
                'value' => '#206bc4',
            ],
        ])
        ->setField([
            'id' => 'link_hover_color',
            'section_id' => 'opt-text-subsection-colors',
            'type' => 'customColor',
            'label' => __('Link hover color'),
            'attributes' => [
                'name' => 'link_hover_color',
                'value' => '#1a569d',
            ],
        ])
        ->setField([
            'id' => 'primary_font',
            'section_id' => 'opt-text-subsection-typography',
            'type' => 'googleFonts',
            'label' => __('Primary font'),
            'attributes' => [
                'name' => 'primary_font',
                'value' => 'Inter',
            ],
        ])
        ->setSection([
            'title' => __('Footer'),
            'id' => 'opt-text-subsection-footer',
            'subsection' => true,
            'icon' => 'ti ti-layout-bottombar',
        ])
        ->setField([
            'id' => 'footer_description',
            'section_id' => 'opt-text-subsection-footer',
            'type' => 'textarea',
            'label' => __('Footer description'),
            'attributes' => [
                'name' => 'footer_description',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'rows' => 3,
                ],
            ],
        ])
        ->setField([
            'id' => 'footer_email',
            'section_id' => 'opt-text-subsection-footer',
            'type' => 'email',
            'label' => __('Footer email'),
            'attributes' => [
                'name' => 'footer_email',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'contact@example.com',
                ],
            ],
        ])
        ->setField([
            'id' => 'footer_phone',
            'section_id' => 'opt-text-subsection-footer',
            'type' => 'text',
            'label' => __('Footer phone'),
            'attributes' => [
                'name' => 'footer_phone',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => '+1 234 567 890',
                ],
            ],
        ])
        ->setField([
            'id' => 'footer_address',
            'section_id' => 'opt-text-subsection-footer',
            'type' => 'text',
            'label' => __('Footer address'),
            'attributes' => [
                'name' => 'footer_address',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => '123 Main Street, City, Country',
                ],
            ],
        ])
        ->setSection([
            'title' => __('Customer Portal'),
            'id' => 'opt-text-subsection-customer-portal',
            'subsection' => true,
            'icon' => 'ti ti-users',
        ])
        ->setField([
            'id' => 'support_email',
            'section_id' => 'opt-text-subsection-customer-portal',
            'type' => 'email',
            'label' => __('Support email'),
            'attributes' => [
                'name' => 'support_email',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'support@example.com',
                ],
            ],
        ])
        ->setField([
            'id' => 'documentation_url',
            'section_id' => 'opt-text-subsection-customer-portal',
            'type' => 'text',
            'label' => __('Documentation URL'),
            'attributes' => [
                'name' => 'documentation_url',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://docs.example.com',
                ],
            ],
        ])
        ->setSection([
            'title' => __('Legal'),
            'id' => 'opt-text-subsection-legal',
            'subsection' => true,
            'icon' => 'ti ti-gavel',
        ])
        ->setField([
            'id' => 'terms_url',
            'section_id' => 'opt-text-subsection-legal',
            'type' => 'text',
            'label' => __('Terms of Service URL'),
            'attributes' => [
                'name' => 'terms_url',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => '/terms',
                ],
            ],
        ])
        ->setField([
            'id' => 'privacy_url',
            'section_id' => 'opt-text-subsection-legal',
            'type' => 'text',
            'label' => __('Privacy Policy URL'),
            'attributes' => [
                'name' => 'privacy_url',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => '/privacy',
                ],
            ],
        ]);
});
