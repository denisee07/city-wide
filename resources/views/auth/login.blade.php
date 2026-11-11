@section('title', 'Sign in — '.config('app.name'))

@push('styles')
<style>
    .aside-brand { display: flex; align-items: center; gap: .7rem; }

    .demo-row {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        margin-top: .55rem; padding: .5rem .6rem;
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        background: var(--panel-2);
    }
    .demo-row .who {
        display: block;
        font-family: var(--mono); font-size: .62rem; letter-spacing: .14em;
        text-transform: uppercase; color: var(--dim);
    }
    .demo-row code { font-family: var(--mono); color: var(--primary); font-size: .76rem; }

    .auth-card .head .eyebrow { margin-bottom: .6rem; }
    .auth-card .btn-block { margin-top: .2rem; }

    .field-icon { position: relative; }
    .field-icon .ico { position: absolute; left: .78rem; top: 50%; transform: translateY(-50%); color: var(--dim); pointer-events: none; }
    .field-icon .input { padding-left: 2.3rem; }

    .aside-foot { font-size: .78rem; color: var(--dim); line-height: 1.7; }
</style>
@endpush

<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<div class="auth-shell">

    {{-- Contrast and text size are needed to read this page, not only after
         getting past it. --}}
    <div class="auth-access"><x-access /></div>

    {{-- ============ brand / perks panel ============ --}}
    <aside class="auth-aside">
        <div>
            <a href="{{ route('home') }}" class="aside-brand">
                <span class="brand-mark" style="width:38px;height:38px;font-size:.9rem;border-radius:11px">BS</span>
                <span>
                    <span class="brand-name" style="display:block">{{ config('app.name') }}</span>
                    <span class="brand-sub">Barangay e-Governance</span>
                </span>
            </a>
        </div>

        <div style="max-width:44ch">
            <span class="eyebrow">Barangay digital front desk</span>

            <h2 style="margin-top:.85rem">
                Skip the queue.<br>
                <span class="gradient-text">Do it online.</span>
            </h2>

            <p class="muted small" style="max-width:42ch">
                One account for complaints, certificates, appointments and queue numbers — with the
                barangay hall open around the clock.
            </p>

            <div class="perks">
                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>File requests in minutes</strong>
                        <span>No paper forms, no line — submit any hour of the day.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Track every step</strong>
                        <span>Reference numbers and a live timeline for each transaction.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Arrive only when needed</strong>
                        <span>Take a queue number from home and watch the board call you.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Stay informed</strong>
                        <span>Announcements, advisories and livelihood listings in one feed.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="aside-foot">
            <div>Open Mon–Fri, 8:00 AM – 5:00 PM &middot; Sat, 8:00 AM – 12:00 NN</div>
            <div>{{ \App\Models\Setting::get('barangay_address', 'Barangay Hall, Philippines') }}</div>
            <div>{{ \App\Models\Setting::get('barangay_contact', '(02) 8000-0000') }}</div>
        </div>
    </aside>

    {{-- ============ sign-in form ============ --}}
    <main class="auth-main">
        <div class="auth-card">
            <div class="head">
                <span class="eyebrow">Resident &amp; official access</span>
                <h1>Welcome back</h1>
                <p>Sign in to file requests, track complaints and book your next appointment.</p>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="field">
                    <label class="label" for="login-email">Email address <span class="req">*</span></label>
                    <div class="field-icon">
                        <x-icon name="mail" size="15" />
                        <input class="input" type="email" id="login-email" name="email"
                               value="{{ old('email') }}" placeholder="you@example.com"
                               autocomplete="email" required autofocus>
                    </div>
                    @error('email')
                        <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="login-password">Password <span class="req">*</span></label>
                    <div class="field-icon">
                        <x-icon name="lock" size="15" />
                        <div class="pw-wrap">
                            <input class="input" type="password" id="login-password" name="password"
                                   placeholder="Your password" autocomplete="current-password" required>
                            @include('partials.password-toggle', ['for' => 'login-password', 'what' => 'password'])
                        </div>
                    </div>
                    @error('password')
                        <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                    @enderror
                </div>

                <label class="check" style="margin:1rem 0 1.2rem">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>Keep me signed in on this device</span>
                </label>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Sign in <x-icon name="login" size="16" />
                </button>
            </form>

            {{-- Demo credentials — local environments only.

                 These ship real credentials in the page source and auto-fill
                 them into the form, which is fine for a localhost demo but must
                 never reach a deployed instance. Gating on APP_ENV keeps them
                 available for testing and grading while removing them entirely
                 from any production build. --}}
            @if (config('app.env') === 'local')
            <div class="demo-box">
                <div class="row items-center" style="gap:.45rem;margin-top:0">
                    <x-icon name="info" size="14" />
                    <strong style="color:var(--text)">Demo accounts</strong>
                    <span class="tiny dim">all use <code>password</code></span>
                </div>

                <div class="demo-row">
                    <span>
                        <span class="who">City admin &mdash; all 18 barangays</span>
                        <code>city.admin@barangay.gov</code>
                    </span>
                    <button type="button" class="btn btn-ghost btn-sm" data-fill-email="city.admin@barangay.gov">Use</button>
                </div>

                <div class="demo-row">
                    <span>
                        <span class="who">Barangay official &mdash; Tagapo only</span>
                        <code>admin@barangay.gov</code>
                    </span>
                    <button type="button" class="btn btn-ghost btn-sm" data-fill-email="admin@barangay.gov">Use</button>
                </div>

                <div class="demo-row">
                    <span>
                        <span class="who">Resident &mdash; Kanluran</span>
                        <code>maria.santos@residents.ph</code>
                    </span>
                    <button type="button" class="btn btn-ghost btn-sm" data-fill-email="maria.santos@residents.ph">Use</button>
                </div>
            </div>
            @endif

            <div class="auth-switch">
                Don&rsquo;t have an account?
                <a href="{{ route('register') }}">Create one</a>
            </div>
        </div>
    </main>
</div>

{{-- flash messages rendered by app.js as toasts --}}
@if (session('success'))
    <div data-flash="{{ session('success') }}" data-flash-type="success" hidden></div>
@endif
@if (session('error'))
    <div data-flash="{{ session('error') }}" data-flash-type="error" hidden></div>
@endif
@if ($errors->any())
    <div data-flash="{{ $errors->first() }}" data-flash-type="error" hidden></div>
@endif

@if (config('app.env') === 'local')
<script>
    /* Demo credential helper — local builds only; see the demo box above. */
    document.querySelectorAll('[data-fill-email]').forEach(function (button) {
        button.addEventListener('click', function () {
            var email = button.getAttribute('data-fill-email');
            var emailField = document.getElementById('login-email');
            var passwordField = document.getElementById('login-password');

            if (emailField) { emailField.value = email; }
            if (passwordField) { passwordField.value = 'password'; }
            if (emailField) { emailField.focus(); }

            if (window.toast) { toast('Demo credentials filled in — press Sign in.', 'info'); }
        });
    });
</script>
@endif

<div id="toasts"></div>
</body>
</html>
