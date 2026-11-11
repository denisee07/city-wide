@props([
    'href',
    'label',
    'icon',
    'pattern',        /* route pattern that makes this row current */
    'count' => null,
])

{{--
    One sidebar row.

    The pattern is passed in rather than derived from the href because an
    admin href can carry a ?barangay= filter, and a query string says nothing
    about which page the user is actually on.
--}}
<a href="{{ $href }}"
    class="nav-item {{ request()->routeIs($pattern) ? 'active' : '' }}"
    @if (request()->routeIs($pattern)) aria-current="page" @endif>
    <x-icon :name="$icon" /> <span>{{ $label }}</span>
    @if (! is_null($count))
        <span class="count">{{ $count }}</span>
    @endif
</a>
