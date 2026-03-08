@extends(Theme::getThemeNamespace('layouts.auth'))

@section('content')
    <h2 class="h3 text-center mb-4">
        {{ trans('plugins/license-manager::customer.auth.reset_password') }}
    </h2>

    {!! $form->renderForm() !!}
@endsection
