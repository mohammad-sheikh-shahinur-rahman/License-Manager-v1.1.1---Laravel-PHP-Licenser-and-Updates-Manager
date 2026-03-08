@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>
                {{ trans('plugins/license-manager::license-manager.tools.run_manual_cron') }}
            </x-core::card.title>
        </x-core::card.header>

        <x-core::card.body>
            <x-core::alert type="info" class="mb-3">
                {{ trans('plugins/license-manager::license-manager.tools.manual_cron_description') }}
            </x-core::alert>

            <x-core::datagrid>
                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.tools.process_license_expirations') }}
                    </x-slot:title>
                    {{ trans('plugins/license-manager::license-manager.tools.process_license_expirations_desc') }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.tools.process_auto_blacklist') }}
                    </x-slot:title>
                    {{ trans('plugins/license-manager::license-manager.tools.process_auto_blacklist_desc') }}
                </x-core::datagrid.item>
            </x-core::datagrid>

            <x-core::form
                method="POST"
                :url="route('lm.manual-cron.run')"
                class="mt-3"
            >
                <x-core::button
                    type="submit"
                    icon="ti ti-player-play"
                    color="primary"
                    id="run-cron-btn"
                >
                    {{ trans('plugins/license-manager::license-manager.tools.run_cron') }}
                </x-core::button>
            </x-core::form>
        </x-core::card.body>
    </x-core::card>
@endsection
