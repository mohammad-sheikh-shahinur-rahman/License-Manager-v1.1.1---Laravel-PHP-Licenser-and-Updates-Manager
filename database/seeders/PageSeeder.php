<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;

class PageSeeder extends BaseSeeder
{
    public function run(): void
    {
        Page::query()->truncate();

        $pages = [
            [
                'name' => 'Terms of Service',
                'content' => $this->getTermsContent(),
                'template' => 'main',
            ],
            [
                'name' => 'Privacy Policy',
                'content' => $this->getPrivacyContent(),
                'template' => 'main',
            ],
        ];

        foreach ($pages as $page) {
            $page = Page::query()->create($page);
            SlugHelper::createSlug($page);
        }
    }

    protected function getTermsContent(): string
    {
        return <<<'HTML'
<h2>1. Acceptance of Terms</h2>
<p>By accessing and using the License Manager service, you agree to be bound by these Terms of Service.</p>

<h2>2. License Usage</h2>
<p>Licenses purchased through our platform are subject to the specific terms of each product. You may not redistribute, resell, or share your license keys without authorization.</p>

<h2>3. Account Responsibilities</h2>
<p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>

<h2>4. Modifications</h2>
<p>We reserve the right to modify these terms at any time. Continued use of the service constitutes acceptance of any modifications.</p>

<h2>5. Contact</h2>
<p>For questions about these terms, please contact our support team.</p>
HTML;
    }

    protected function getPrivacyContent(): string
    {
        return <<<'HTML'
<h2>1. Information We Collect</h2>
<p>We collect information you provide directly to us, including name, email address, and license usage data.</p>

<h2>2. How We Use Your Information</h2>
<p>We use the information we collect to provide and improve our services, process transactions, and communicate with you.</p>

<h2>3. Data Security</h2>
<p>We implement appropriate security measures to protect your personal information against unauthorized access or disclosure.</p>

<h2>4. Data Retention</h2>
<p>We retain your information for as long as your account is active or as needed to provide you services.</p>

<h2>5. Contact</h2>
<p>For privacy-related inquiries, please contact our support team.</p>
HTML;
    }
}
