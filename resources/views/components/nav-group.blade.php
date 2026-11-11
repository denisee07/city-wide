@props([
    'label',
    'icon',
    'id',
    'active' => false,
])

{{--
    A collapsible section of the sidebar: one row that opens to reveal the
    related pages underneath it, so the panel offers a handful of decisions
    instead of laying every destination out at once.

    It is server-rendered open whenever one of its children is the current
    page, which means it works with JS switched off; app.js then remembers
    which groups the officer opened, and keeps the active one visible.
--}}
<div class="nav-group {{ $active ? 'open' : '' }}" data-nav-group="{{ $id }}">
    <button type="button" class="nav-item nav-toggle" data-nav-toggle
        aria-expanded="{{ $active ? 'true' : 'false' }}" aria-controls="nav-{{ $id }}">
        <x-icon :name="$icon" /> <span>{{ $label }}</span>
        <x-icon name="chevron" size="14" class="nav-caret" />
    </button>

    <div class="nav-sub" id="nav-{{ $id }}">
        <div class="nav-sub-inner">
            {{ $slot }}
        </div>
    </div>
</div>
