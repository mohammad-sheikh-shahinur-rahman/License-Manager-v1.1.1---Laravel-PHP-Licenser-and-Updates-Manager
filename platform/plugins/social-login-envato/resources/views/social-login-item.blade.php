<li>
    <a
        class="envato"
        data-bs-toggle="tooltip"
        data-bs-title="{{ $label = trans('plugins/social-login::social-login.sign_in_with', ['provider' => 'Envato']) }}"
        title="{{ $label }}"
        href="{{ $url }}"
    >
        <x-core::icon name="ti ti-brand-envato" />
        <span>{{ $label }}</span>
    </a>
</li>
