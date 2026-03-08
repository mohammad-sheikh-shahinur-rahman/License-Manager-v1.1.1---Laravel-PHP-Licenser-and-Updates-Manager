# License-Manager-v1.1.1---Laravel-PHP-Licenser-and-Updates-Manager
License Manager is a powerful standalone software licensing solution built with Laravel. It enables you to manage products, licenses, activations, and customers with a complete REST API, webhook notifications, Envato integration, and a self-service customer portal.
== Installation Guide ==

=== Requirements ===

* PHP version 8.2 or higher
* MySQL 5.7+ or MariaDB 10.2+
* Laravel 12 (included)

=== Installation Steps ===

1. Download from your CodeCanyon downloads page
2. Extract the downloaded zip file to your server
3. Access https://your-domain.com/install to run the web installer
4. Follow the installer steps to configure database and admin account
5. Configure settings in License Manager > Settings

After installation, you can access all features from the admin panel.

== Key Features ==

* Product Management - Define products with unique reference IDs, version tracking, and changelogs
* License Generation - Create licenses with multiple types (perpetual, subscription, trial)
* Parallel Usage Limits - Control how many activations per license
* Activation Tracking - Track activations by domain and IP with auto-deactivation
* Customer Portal - Self-service portal for customers to view licenses and manage activations
* Social Login - Sign in with Envato, Google, Facebook, or GitHub accounts
* REST API - Complete API for license activation, verification, and updates
* Webhook Notifications - Real-time notifications for license events with HMAC verification
* Enterprise Security - AES-128/256 encryption, domain normalization, IP validation
* Auto-blacklisting - Automatically block suspicious activity and failed attempts
* Envato Integration - Verify Envato purchase codes and auto-import licenses
* Multi-language Support - Translations available for 15+ languages
* Role-based Permissions - Granular permission settings for admin roles
* Activity Logging - Track all license operations and admin actions

== Support ==

We're committed to providing excellent support for our customers. If you encounter any issues or have questions about License Manager, please don't hesitate to reach out:

1. Check our documentation first: https://docs.botble.com/license-manager
2. If you can't find an answer in the documentation, please submit a support ticket at https://botble.ticksy.com
3. Our support team typically responds within 12-24 hours during business days

== License ==

License Manager is licensed under the Botble License. You are allowed to use this application on a single domain. For use on multiple domains, you need to purchase additional licenses.

== Updates ==

We regularly release updates with new features, improvements, and bug fixes. To update your installation:

1. Download the latest version from your CodeCanyon downloads page
2. Backup your current installation and database
3. Replace files with the new version (preserve your .env file)
4. Run php artisan migrate to update database
5. Clear cache with php artisan cache:clear

Thank you for choosing License Manager!

