<?php

namespace Botble\LicenseManager;

use Botble\LicenseManager\Encryption\LegacyEncrypter;
use Botble\LicenseManager\Models\ApiKey;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use RuntimeException;

class LicenseManager
{
    protected ?string $clientUrl = null;

    protected ?string $clientIp = null;

    protected ?string $clientLocale = null;

    protected ?ApiKey $clientApiKey = null;

    public function getClientUrl(): ?string
    {
        return $this->clientUrl;
    }

    public function getClientDomain(): ?string
    {
        if (! $this->getClientUrl()) {
            return null;
        }

        return parse_url($this->getClientUrl(), PHP_URL_HOST);
    }

    public function getNormalizedClientDomain(): ?string
    {
        $domain = $this->getClientDomain();

        if (! $domain) {
            return null;
        }

        return $this->normalizeDomain($domain);
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if (setting('lm_normalize_domain_variants', true)) {
            $domain = preg_replace('#^https?://#', '', $domain);
            $domain = explode('/', $domain)[0];

            if (str_starts_with($domain, 'www.')) {
                $domain = substr($domain, 4);
            }
        }

        return $domain;
    }

    public function extractDomainFromUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        return $this->normalizeDomain($host);
    }

    public function setClientUrl(?string $clientUrl): void
    {
        $this->clientUrl = $this->normalizeClientUrl($clientUrl);
    }

    /**
     * Normalize client URL by removing /public from the path.
     */
    protected function normalizeClientUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);

        if (! isset($parsed['host'])) {
            return $url;
        }

        $path = $parsed['path'] ?? '';

        // Remove /public or /public/ from the end of the path
        $path = preg_replace('#/public/?$#', '', $path);

        // Reconstruct URL
        $normalized = ($parsed['scheme'] ?? 'https') . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $normalized .= ':' . $parsed['port'];
        }

        if ($path && $path !== '/') {
            $normalized .= $path;
        }

        return $normalized;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getClientLocale(): ?string
    {
        return $this->clientLocale;
    }

    public function setClientLocale(?string $clientLocale): void
    {
        $this->clientLocale = $clientLocale;
    }

    public function getClientApiKey(): ?ApiKey
    {
        return $this->clientApiKey;
    }

    public function setClientApiKey(?ApiKey $clientApiKey): void
    {
        $this->clientApiKey = $clientApiKey;
    }

    public function generateEncryptionKey(string $cipher): string
    {
        if ($cipher === 'legacy') {
            throw new RuntimeException(trans('plugins/license-manager::license-manager.general.unsupported_cipher'));
        }

        $key = Encrypter::generateKey($cipher);

        return 'base64:' . base64_encode($key);
    }

    public function createEncrypter(): Encrypter
    {
        $cipher = setting('lm_license_encryption_cipher', 'aes-128-cbc');
        $key = setting('lm_license_encryption_key');

        if (! $key) {
            throw new RuntimeException(trans('plugins/license-manager::license-manager.general.encryption_key_not_set'));
        }

        $key = Str::of($key)->afterLast('base64:')->toString();
        $key = base64_decode($key);

        return new Encrypter($key, $cipher);
    }

    public function createLegacyEncrypter(): ?LegacyEncrypter
    {
        $key = setting('lm_legacy_encryption_key');

        if (! $key) {
            return null;
        }

        return new LegacyEncrypter($key);
    }

    public function hasLegacyEncryption(): bool
    {
        return ! empty(setting('lm_legacy_encryption_key'));
    }

    /**
     * Check if a domain matches any of the allowed domains.
     * Normalizes both the client domain and allowed domains for comparison.
     * Allows http/https and www/non-www variations.
     *
     * @param  array<string>  $allowedDomains
     */
    public function isDomainAllowed(?string $clientDomain, array $allowedDomains): bool
    {
        if (empty($allowedDomains) || ! $clientDomain) {
            return true;
        }

        $normalizedClient = $this->normalizeDomain($clientDomain);

        foreach ($allowedDomains as $allowed) {
            $normalizedAllowed = $this->extractDomainFromUrl(trim($allowed))
                ?? $this->normalizeDomain(trim($allowed));

            if ($normalizedClient === $normalizedAllowed) {
                return true;
            }
        }

        return false;
    }
}
