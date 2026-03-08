@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/license-manager::license-manager.php_obfuscator.source_code') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    @if (session('error_message'))
                        <x-core::alert type="danger" class="mb-3">
                            {{ session('error_message') }}
                        </x-core::alert>
                    @endif

                    <x-core::form
                        method="POST"
                        :url="route('lm.php-obfuscator')"
                    >
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <x-core::form.select
                                    name="obfuscation_type"
                                    :label="trans('plugins/license-manager::license-manager.php_obfuscator.obfuscation_type')"
                                    :options="[
                                        'lite' => trans('plugins/license-manager::license-manager.php_obfuscator.lite_obfuscation'),
                                        'advanced' => trans('plugins/license-manager::license-manager.php_obfuscator.advanced_obfuscation'),
                                    ]"
                                    :value="old('obfuscation_type', 'lite')"
                                    required
                                />
                            </div>
                            <div class="col-md-6">
                                <x-core::form.select
                                    name="minify_html"
                                    :label="trans('plugins/license-manager::license-manager.php_obfuscator.minify_html')"
                                    :options="[
                                        '0' => trans('plugins/license-manager::license-manager.php_obfuscator.dont_minify'),
                                        '1' => trans('plugins/license-manager::license-manager.php_obfuscator.minify'),
                                    ]"
                                    :value="old('minify_html', '0')"
                                />
                            </div>
                        </div>

                        <x-core::form.textarea
                            name="source_code"
                            :label="trans('plugins/license-manager::license-manager.php_obfuscator.php_source_code')"
                            :value="$sourceCode"
                            :placeholder="trans('plugins/license-manager::license-manager.php_obfuscator.paste_code_placeholder')"
                            rows="20"
                            required
                        />

                        <x-core::alert type="info" class="mb-3">
                            {{ trans('plugins/license-manager::license-manager.php_obfuscator.source_code_hint') }}
                        </x-core::alert>

                        <x-core::button type="submit" icon="ti ti-lock" color="primary">
                            {{ trans('plugins/license-manager::license-manager.php_obfuscator.obfuscate') }}
                        </x-core::button>
                    </x-core::form>
                </x-core::card.body>
            </x-core::card>
        </div>

        <div class="col-lg-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/license-manager::license-manager.php_obfuscator.obfuscated_code') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    @if ($obfuscatedCode)
                        <x-core::form.code-editor
                            name="obfuscated_code"
                            :value="$obfuscatedCode"
                            mode="php"
                            readonly
                        />
                        <div class="d-flex justify-content-end mt-3">
                            <x-core::button
                                type="button"
                                icon="ti ti-copy"
                                color="primary"
                                onclick="navigator.clipboard.writeText(document.querySelector('[name=obfuscated_code]').value); Botble.showSuccess('{{ trans('core/base::base.copy_success') }}')"
                            >
                                {{ trans('core/base::base.copy') }}
                            </x-core::button>
                        </div>
                    @else
                        <x-core::alert type="secondary">
                            {{ trans('plugins/license-manager::license-manager.php_obfuscator.code_placeholder') }}
                        </x-core::alert>
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection
