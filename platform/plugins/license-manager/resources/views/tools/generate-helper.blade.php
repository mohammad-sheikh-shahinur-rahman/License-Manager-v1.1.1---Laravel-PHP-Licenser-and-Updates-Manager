@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/license-manager::license-manager.tools.generate_helper') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::form
                        method="POST"
                        :url="route('lm.generate-helper')"
                    >
                        <x-core::form.select
                            name="helper_type"
                            :label="trans('plugins/license-manager::license-manager.tools.helper_type')"
                            :options="[
                                'external' => trans('plugins/license-manager::license-manager.tools.external_helper'),
                                'internal' => trans('plugins/license-manager::license-manager.tools.internal_helper'),
                            ]"
                            :value="$helperType"
                            onchange="toggleHelperFields(this.value)"
                            required
                        />

                        <div id="external-fields" style="{{ $helperType === 'external' ? '' : 'display: none;' }}">
                            <x-core::form.select
                                name="product_id"
                                :label="trans('plugins/license-manager::license-manager.tools.product')"
                                :options="$products"
                                :placeholder="trans('plugins/license-manager::license-manager.tools.select_product')"
                            />

                            <x-core::form.select
                                name="api_key"
                                id="external_api_key"
                                :label="trans('plugins/license-manager::license-manager.tools.api_key')"
                                :options="$externalApiKeys"
                                :placeholder="trans('plugins/license-manager::license-manager.tools.select_api_key')"
                                :helper-text="trans('plugins/license-manager::license-manager.tools.external_api_key_hint')"
                            />

                            @php
                                $verifyTypes = ['non_envato' => trans('plugins/license-manager::license-manager.tools.non_envato')];
                                if (setting('lm_enable_envato_integration') || setting('envato_personal_token')) {
                                    $verifyTypes['envato'] = trans('plugins/license-manager::license-manager.tools.envato');
                                }
                            @endphp

                            <x-core::form.select
                                name="verify_type"
                                :label="trans('plugins/license-manager::license-manager.tools.verify_type')"
                                :options="$verifyTypes"
                            />

                            <x-core::form.select
                                name="verification_period"
                                :label="trans('plugins/license-manager::license-manager.tools.verification_period')"
                                :options="[
                                    '0' => trans('plugins/license-manager::license-manager.tools.disabled'),
                                    '1' => trans('plugins/license-manager::license-manager.tools.every_day'),
                                    '3' => trans('plugins/license-manager::license-manager.tools.every_3_days'),
                                    '7' => trans('plugins/license-manager::license-manager.tools.every_week'),
                                    '30' => trans('plugins/license-manager::license-manager.tools.every_month'),
                                    '90' => trans('plugins/license-manager::license-manager.tools.every_3_months'),
                                    '365' => trans('plugins/license-manager::license-manager.tools.every_year'),
                                ]"
                                :helper-text="trans('plugins/license-manager::license-manager.tools.verification_period_hint')"
                            />
                        </div>

                        <div id="internal-fields" style="{{ $helperType === 'internal' ? '' : 'display: none;' }}">
                            <x-core::form.select
                                name="api_key"
                                id="internal_api_key"
                                :label="trans('plugins/license-manager::license-manager.tools.api_key')"
                                :options="$internalApiKeys"
                                :placeholder="trans('plugins/license-manager::license-manager.tools.select_api_key')"
                                :helper-text="trans('plugins/license-manager::license-manager.tools.internal_api_key_hint')"
                            />
                        </div>

                        <x-core::button type="submit" icon="ti ti-code" color="primary">
                            {{ trans('plugins/license-manager::license-manager.tools.generate') }}
                        </x-core::button>
                    </x-core::form>
                </x-core::card.body>
            </x-core::card>
        </div>

        <div class="col-lg-8">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/license-manager::license-manager.tools.generated_code') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    @if ($generatedCode)
                        <x-core::form.code-editor
                            name="generated_code"
                            :value="$generatedCode"
                            mode="php"
                            readonly
                        />
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <x-core::alert type="info" class="mb-0 flex-grow-1 me-3">
                                @if ($helperType === 'external')
                                    {{ trans('plugins/license-manager::license-manager.tools.external_helper_instructions') }}
                                @else
                                    {{ trans('plugins/license-manager::license-manager.tools.internal_helper_instructions') }}
                                @endif
                            </x-core::alert>
                            <x-core::button
                                type="button"
                                icon="ti ti-copy"
                                color="primary"
                                onclick="navigator.clipboard.writeText(document.querySelector('[name=generated_code]').value); Botble.showSuccess('{{ trans('core/base::base.copy_success') }}')"
                            >
                                {{ trans('core/base::base.copy') }}
                            </x-core::button>
                        </div>
                    @else
                        <x-core::alert type="secondary">
                            {{ trans('plugins/license-manager::license-manager.tools.code_placeholder') }}
                        </x-core::alert>
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>

    <script>
        function toggleHelperFields(type) {
            const externalFields = document.getElementById('external-fields');
            const internalFields = document.getElementById('internal-fields');
            const externalApiKey = document.getElementById('external_api_key');
            const internalApiKey = document.getElementById('internal_api_key');

            if (type === 'external') {
                externalFields.style.display = '';
                internalFields.style.display = 'none';
                if (externalApiKey) externalApiKey.name = 'api_key';
                if (internalApiKey) internalApiKey.name = '';
            } else {
                externalFields.style.display = 'none';
                internalFields.style.display = '';
                if (externalApiKey) externalApiKey.name = '';
                if (internalApiKey) internalApiKey.name = 'api_key';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleHelperFields('{{ $helperType }}');
        });
    </script>
@endsection
