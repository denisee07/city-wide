@php
    $portal = $portal ?? 'resident';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<div class="shell">

    {{-- ============ sidebar ============ --}}
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">BS</div>
            <div>
                <div class="brand-name">{{ config('app.name') }}</div>
                <div class="brand-sub">{{ $portal === 'admin' ? 'Officials Console' : 'Resident Portal' }}</div>
            </div>
        </div>

        @include('layouts.nav.'.$portal)

        <div class="sidebar-foot">
            {{-- Account and exit controls live together at the bottom, the
                 place people already look for them. --}}
            @if ($portal === 'resident')
                <a href="{{ route('resident.profile.edit') }}" class="nav-item {{ request()->routeIs('resident.profile.*') ? 'active' : '' }}">
                    <x-icon name="user" /> <span>My profile</span>
                </a>
            @endif
            <a href="{{ route('home') }}" class="nav-item">
                <x-icon name="compass" /> <span>Public website</span>
            </a>
        </div>
    </aside>
    <div class="sidebar-backdrop"></div>

    {{-- ============ main ============ --}}
    <div class="main">
        <header class="topbar">
            <button class="hamburger" data-sidebar-toggle aria-label="Toggle navigation">
                <x-icon name="menu" size="20" />
            </button>

            <div class="relative grow" style="min-width:0">
                <div class="topbar-crumb">{{ $portal === 'admin' ? 'Barangay administration' : 'Resident services' }}</div>
                <div class="topbar-title">@yield('topbar-title', 'Dashboard')</div>
            </div>

            {{-- An officer should never have to guess whose records are on
                 screen, so the scope travels with them on every page. It is a
                 view they chose, not a permission: anyone can widen it. --}}
            @if ($portal === 'admin')
                @php
                    $filterId = \App\Support\BarangayFilter::current();
                    $scopeName = $filterId
                        ? (\App\Models\Barangay::query()->whereKey($filterId)->value('name') ?? 'All 18 barangays')
                        : 'All 18 barangays';
                @endphp
                <span class="badge badge-violet shrink-0"
                      title="Every list on this console is showing {{ $scopeName }}">
                    {{ $scopeName }}
                    @if ($filterId)
                        <a href="{{ url()->current() }}"
                           class="tiny"
                           style="margin-left:.4rem;opacity:.75"
                           title="Show all barangays again">clear &times;</a>
                    @endif
                </span>
            @endif

            @hasSection('topbar-actions')
                <div class="row shrink-0">@yield('topbar-actions')</div>
            @endif

            {{-- The avatar reads as "my account", so it must never sign someone
                 out behind their back. It now only shows who is signed in -
                 and opens the profile in the resident portal - while signing
                 out is its own, unmistakably labelled control. --}}
            @if ($portal === 'resident')
                <a href="{{ route('resident.profile.edit') }}" class="user-pill shrink-0" title="Open my profile">
                    <span class="avatar sm">{{ auth()->user()->initials }}</span>
                    <x-icon name="user" size="16" />
                </a>
            @else
                <span class="user-pill shrink-0" title="Signed in as {{ auth()->user()->name }}">
                    <span class="avatar sm">{{ auth()->user()->initials }}</span>
                </span>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" title="Sign out of this account">
                    <x-icon name="logout" size="15" /> <span class="signout-label">Sign out</span>
                </button>
            </form>
        </header>

        <main class="content">
            @if (session('success'))
                <div data-flash="{{ session('success') }}" data-flash-type="success" hidden></div>
            @endif
            @if (session('error'))
                <div data-flash="{{ session('error') }}" data-flash-type="error" hidden></div>
            @endif
            @if ($errors->any())
                <div data-flash="{{ $errors->first() }}" data-flash-type="error" hidden></div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div id="toasts"></div>
@stack('modals')
</body>
</html>
