@extends(Theme::getThemeNamespace('layouts.auth'))

@section('content')
    <h2 class="h3 text-center mb-4">
        {{ trans('plugins/license-manager::customer.auth.forgot_password.title') }}
    </h2>

    {!! $form->renderForm() !!}
@endsection
