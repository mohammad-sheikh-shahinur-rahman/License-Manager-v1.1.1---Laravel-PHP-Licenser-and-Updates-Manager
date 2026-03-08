<ul class="menu">
    @foreach (DashboardMenu::getAll('license-manager-customer') as $item)
        @continue(! $item['name'])

        <li>
            <a
                href="{{ $item['url']  }}"
                title="{{ $item['name'] }}"
                @class(['active' => $item['active'], 'd-flex align-items-center'])
            >
                <x-core::icon :name="$item['icon']" class="me-2" />
                {{ $item['name'] }}
            </a>
        </li>
    @endforeach
</ul>
