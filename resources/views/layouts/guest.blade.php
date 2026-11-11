<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<nav class="site-nav">
    <a href="{{ route('home') }}" class="row items-center" style="gap:.7rem">
        <span class="brand-mark" style="width:34px;height:34px;font-size:.82rem;border-radius:10px">BS</span>
        <span>
            <span class="brand-name" style="display:block">{{ config('app.name') }}</span>
            <span class="brand-sub">Barangay e-Governance</span>
        </span>
    </a>

    <div class="links">
        {{-- Available before sign-in: someone who needs high contrast or larger
             type needs it to read the login screen, not only after. --}}
        <x-access />
        {{-- In-page anchors are rooted at the home page, otherwise clicking
             them from /services just appends a fragment to /services and
             nothing moves - confusing for a first-time visitor. --}}
        <a href="{{ route('home') }}#services">Services</a>
        <a href="{{ route('home') }}#how">How it works</a>
        <a href="{{ route('home') }}#announcements">Announcements</a>
        <a href="{{ route('login') }}">Sign in</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Create account</a>
    </div>
</nav>

@yield('content')

<footer class="site-footer">
    <div class="cols">
        <div>
            <div class="row items-center" style="gap:.6rem;margin-bottom:.8rem">
                <span class="brand-mark" style="width:34px;height:34px;font-size:.82rem;border-radius:10px">BS</span>
                <strong>{{ config('app.name') }}</strong>
            </div>
            <p class="small muted" style="max-width:34ch">
                A unified digital front desk for barangay transactions — complaints, certificates,
                appointments, queueing and community updates in one place.
            </p>
        </div>

        <div>
            <h4>Resident services</h4>
            <ul>
                <li><a href="{{ route('resident.complaints.create') }}">File a complaint</a></li>
                <li><a href="{{ route('resident.documents.create') }}">Request a certificate</a></li>
                <li><a href="{{ route('resident.appointments.create') }}">Book an appointment</a></li>
                <li><a href="{{ route('resident.queue.index') }}">Take a queue number</a></li>
            </ul>
        </div>

        <div>
            <h4>Community</h4>
            <ul>
                <li><a href="{{ route('resident.announcements.index') }}">Announcements</a></li>
                <li><a href="{{ route('resident.freedom-wall.index') }}">Freedom wall</a></li>
                <li><a href="{{ route('resident.lost-found.index') }}">Lost &amp; found</a></li>
                <li><a href="{{ route('resident.jobs.index') }}">Livelihood &amp; jobs</a></li>
            </ul>
        </div>

        <div>
            <h4>Barangay hall</h4>
            <ul>
                <li>Open Mon–Fri, 8:00 AM – 5:00 PM</li>
                <li>Saturday, 8:00 AM – 12:00 NN</li>
                <li>{{ \App\Models\Setting::get('barangay_address', 'Barangay Hall, Philippines') }}</li>
                <li>{{ \App\Models\Setting::get('barangay_contact', '(02) 8000-0000') }}</li>
            </ul>
        </div>
    </div>

    <div class="copyright">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
        <span class="mono tiny">Powered by Laravel &middot; {{ config('app.env') }} build</span>
    </div>
</footer>

<div id="toasts"></div>
@stack('modals')
</body>
</html>
