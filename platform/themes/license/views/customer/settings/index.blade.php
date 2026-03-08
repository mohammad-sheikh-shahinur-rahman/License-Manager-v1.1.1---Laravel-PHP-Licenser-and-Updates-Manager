@extends(Theme::getThemeNamespace('layouts.main'))

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::tab class="card-header-tabs">
                <x-core::tab.item
                    id="basic-setting"
                    :label="trans('plugins/license-manager::customer.basic_setting')"
                    :is-active="true"
                />
                <x-core::tab.item
                    id="avatar-setting"
                    :label="trans('plugins/license-manager::customer.avatar_setting')"
                />
                <x-core::tab.item
                    id="password-setting"
                    :label="trans('plugins/license-manager::customer.password_setting')"
                />

                {!! apply_filters('lm_customer_setting_menu', null) !!}
            </x-core::tab>
        </x-core::card.header>

        <x-core::card.body>
            <x-core::tab.content>
                <x-core::tab.pane id="basic-setting" :is-active="true">
                    {!! $basicSettingForm->renderForm() !!}
                </x-core::tab.pane>
                <x-core::tab.pane id="avatar-setting">
                    <x-core::crop-image
                        :label="trans('plugins/license-manager::customer.avatar_form.avatar')"
                        name="avatar_file"
                        :value="Auth::user()->avatar_url"
                        :action="route('lm.customer.settings.avatar')"
                    />
                </x-core::tab.pane>
                <x-core::tab.pane id="password-setting">
                    {!! $passwordSettingForm->renderForm() !!}
                </x-core::tab.pane>

                {!! apply_filters('lm_customer_setting_content', null) !!}
            </x-core::tab.content>
        </x-core::card.body>
    </x-core::card>
@endsection
