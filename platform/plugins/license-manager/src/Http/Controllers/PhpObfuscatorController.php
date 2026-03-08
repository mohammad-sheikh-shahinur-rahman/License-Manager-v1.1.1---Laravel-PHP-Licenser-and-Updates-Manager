<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Services\PhpObfuscatorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhpObfuscatorController extends LicenseManagerController
{
    public function index(Request $request, PhpObfuscatorService $obfuscatorService): View
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.php_obfuscator.title'));

        $obfuscatedCode = null;
        $sourceCode = '';

        if ($request->isMethod('post')) {
            $request->validate([
                'source_code' => ['required', 'string'],
                'obfuscation_type' => ['required', 'in:lite,advanced'],
                'minify_html' => ['nullable', 'in:0,1'],
            ]);

            $sourceCode = $request->input('source_code');
            $obfuscationType = $request->input('obfuscation_type', 'lite');
            $minifyHtml = (bool) $request->input('minify_html', false);

            $result = $obfuscatorService->obfuscate($sourceCode, $obfuscationType, $minifyHtml);

            if ($result['success']) {
                $obfuscatedCode = $result['obfuscated'];
            } else {
                session()->flash('error_message', $result['message']);
            }
        }

        return view('plugins/license-manager::tools.php-obfuscator', compact(
            'obfuscatedCode',
            'sourceCode'
        ));
    }
}
