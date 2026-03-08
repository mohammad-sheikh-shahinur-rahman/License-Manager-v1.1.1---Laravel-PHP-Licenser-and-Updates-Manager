<?php

namespace Botble\LicenseManager\Services;

class PhpObfuscatorService
{
    public function obfuscate(string $code, string $type = 'lite', bool $minifyHtml = false): array
    {
        if (empty(trim($code))) {
            return [
                'success' => false,
                'message' => trans('plugins/license-manager::license-manager.php_obfuscator.empty_code'),
            ];
        }

        if (! $this->isValidPhpCode($code)) {
            return [
                'success' => false,
                'message' => trans('plugins/license-manager::license-manager.php_obfuscator.invalid_php_code'),
            ];
        }

        if ($minifyHtml) {
            $code = $this->minifyHtml($code);
        }

        $obfuscatedCode = match ($type) {
            'advanced' => $this->advancedObfuscation($code),
            default => $this->liteObfuscation($code),
        };

        return [
            'success' => true,
            'obfuscated' => $obfuscatedCode,
        ];
    }

    protected function isValidPhpCode(string $code): bool
    {
        return str_contains($code, '<?php') || str_contains($code, '<?');
    }

    protected function liteObfuscation(string $code): string
    {
        $code = $this->removePhpTags($code);

        $encoded = base64_encode($code);

        return "<?php eval(base64_decode('{$encoded}')); ?>";
    }

    protected function advancedObfuscation(string $code): string
    {
        $code = $this->removePhpTags($code);

        $encoded = base64_encode(gzdeflate($code, 9));

        $varName = $this->generateRandomVarName();
        $funcName = $this->generateRandomVarName();

        return "<?php \${$varName}=base64_decode('{$encoded}');\${$funcName}=gzinflate(\${$varName});eval(\${$funcName}); ?>";
    }

    protected function removePhpTags(string $code): string
    {
        $code = preg_replace('/^<\?php\s*/i', '', $code);
        $code = preg_replace('/^<\?\s*/', '', $code);
        $code = preg_replace('/\s*\?>$/', '', $code);

        return $code;
    }

    protected function generateRandomVarName(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle($chars), 0, 8);
    }

    protected function minifyHtml(string $code): string
    {
        $search = [
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s',
            '/<!--(.|\s)*?-->/',
        ];

        $replace = ['>', '<', '\\1', ''];

        return preg_replace($search, $replace, $code);
    }
}
