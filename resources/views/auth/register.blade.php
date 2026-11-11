@section('title', 'Create an account — '.config('app.name'))

@push('styles')
<style>
    .aside-brand { display: flex; align-items: center; gap: .7rem; }

    .auth-card.wide { max-width: 560px; }
    .auth-card .head .eyebrow { display: flex; width: fit-content; margin-bottom: .6rem; }

    .terms-box {
        display: flex; align-items: flex-start; gap: .7rem;
        margin-top: .3rem; padding: .85rem .95rem;
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        background: var(--panel-2); font-size: .84rem; color: var(--muted);
    }
    .terms-box input { margin-top: .22rem; accent-color: var(--primary); width: 15px; height: 15px; flex-shrink: 0; }
    .terms-box a { font-weight: 600; }

    .aside-foot { font-size: .78rem; color: var(--dim); line-height: 1.7; }
    .divider-or { display: flex; align-items: center; gap: .8rem; margin: 1.3rem 0; color: var(--dim); font-family: var(--mono); font-size: .66rem; letter-spacing: .16em; text-transform: uppercase; }
    .divider-or::before, .divider-or::after { content: ""; height: 1px; flex: 1; background: var(--border); }
</style>
@endpush

<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<div class="auth-shell">

    {{-- Same reasoning as the login page. --}}
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
            <span class="eyebrow">Join the digital barangay</span>

            <h2 style="margin-top:.85rem">
                Your barangay ID,<br>
                <span class="gradient-text">now in your pocket.</span>
            </h2>

            <p class="muted small" style="max-width:42ch">
                Registration takes under two minutes. Verify once at the barangay hall and every
                transaction after that is only a few clicks.
            </p>

            <div class="perks">
                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>All eight services, one login</strong>
                        <span>Complaints, certificates, appointments, queue, wall, lost &amp; found, jobs and map.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Verified residents only</strong>
                        <span>Your purok and address keep barangay records accurate and trustworthy.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Anonymous when it matters</strong>
                        <span>Post on the freedom wall without exposing your identity to neighbours.</span>
                    </div>
                </div>

                <div class="perk">
                    <span class="tick"><x-icon name="check" size="14" /></span>
                    <div>
                        <strong>Free for residents</strong>
                        <span>No subscription — only the printed document fees at the counter.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="aside-foot">
            <div>{{ \App\Models\Setting::get('barangay_motto', 'Makabago, Mapagkalinga, Maaasahan.') }}</div>
            <div>{{ \App\Models\Setting::get('barangay_capain', 'Punong Barangay') }}</div>
            <div>{{ \App\Models\Setting::get('barangay_contact', '(02) 8000-0000') }}</div>
        </div>
    </aside>

    {{-- ============ registration form ============ --}}
    <main class="auth-main">
        <div class="auth-card wide">
            <div class="head">
                <span class="eyebrow">Resident registration</span>
                <h1>Create your account</h1>
                <p>Fill in your details once — the barangay will verify them at the hall or on your first visit.</p>
            </div>

            <form method="POST" action="{{ route('register.attempt') }}">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="reg-name">Full name <span class="req">*</span></label>
                        <input class="input" type="text" id="reg-name" name="name"
                               value="{{ old('name') }}" placeholder="Juan D. Reyes"
                               maxlength="120" autocomplete="name" required autofocus>
                        @error('name')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="reg-email">Email address <span class="req">*</span></label>
                        <input class="input" type="email" id="reg-email" name="email"
                               value="{{ old('email') }}" placeholder="you@example.com"
                               maxlength="190" autocomplete="email" required>
                        @error('email')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="reg-phone">Mobile number</label>
                        <input class="input" type="text" id="reg-phone" name="phone"
                               value="{{ old('phone') }}" placeholder="09XX XXX XXXX"
                               maxlength="30" autocomplete="tel">
                        @error('phone')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="reg-birth">Birth date</label>
                        <input class="input" type="date" id="reg-birth" name="birth_date"
                               value="{{ old('birth_date') }}" max="{{ now()->subDay()->toDateString() }}">
                        @error('birth_date')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="reg-barangay">Barangay <span class="req">*</span></label>
                        <select class="select" id="reg-barangay" name="barangay_id" required
                                aria-describedby="reg-barangay-help">
                            <option value="">Choose your barangay…</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}" @selected((string) old('barangay_id') === (string) $barangay->id)>
                                    {{ $barangay->label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="help" id="reg-barangay-help">
                            Complaints, announcements and job listings are shown for your barangay only.
                        </div>
                        @error('barangay_id')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="reg-purok">Purok</label>
                        <select class="select" id="reg-purok" name="purok">
                            <option value="">Select your purok</option>
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="Purok {{ $i }}" @selected(old('purok') === 'Purok '.$i)>
                                    Purok {{ $i }}
                                </option>
                            @endfor
                        </select>
                        @error('purok')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="reg-address">Complete address</label>
                        <input class="input" type="text" id="reg-address" name="address"
                               value="{{ old('address') }}" placeholder="House no., street, zone"
                               maxlength="190" autocomplete="street-address">
                        @error('address')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="reg-password">Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input class="input" type="password" id="reg-password" name="password"
                                   placeholder="At least 8 characters"
                                   minlength="8" maxlength="64"
                                   autocomplete="new-password" required
                                   aria-describedby="pw-meter-hint pw-meter-rules">
                            @include('partials.password-toggle', ['for' => 'reg-password', 'what' => 'password'])
                        </div>

                        {{-- live strength meter - rules mirror App\Support\PasswordPolicy --}}
                        @include('partials.password-strength', [
                            'pwId'      => 'reg-password',
                            'confirmId' => 'reg-password-confirmation',
                            'matchId'   => 'pw-match',
                            'meterId'   => 'pw-meter',
                            'email'     => null,
                            'optional'  => false,
                        ])

                        @foreach ($errors->get('password') as $message)
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @endforeach
                    </div>

                    <div class="field span-2">
                        <label class="label" for="reg-password-confirmation">Confirm password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input class="input" type="password" id="reg-password-confirmation" name="password_confirmation"
                                   placeholder="Repeat your password"
                                   minlength="8" maxlength="64"
                                   autocomplete="new-password" required
                                   aria-describedby="pw-match">
                            @include('partials.password-toggle', ['for' => 'reg-password-confirmation', 'what' => 'password confirmation'])
                        </div>
                        <div class="pw-match" id="pw-match" role="status" hidden></div>
                        @error('password_confirmation')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="terms-box">
                            <input type="checkbox" name="terms" value="1" @checked(old('terms'))>
                            <span>
                                I agree to the barangay&rsquo;s data privacy notice and acceptable use policy.
                                My details are used only to process barangay transactions and to verify my residency.
                            </span>
                        </label>
                        @error('terms')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.2rem">
                    Create account <x-icon name="chevron" size="16" />
                </button>
            </form>

            <div class="divider-or">Already registered?</div>

            <div class="auth-switch" style="margin-top:0">
                Have an account?
                <a href="{{ route('login') }}">Sign in instead</a>
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

<div id="toasts"></div>
</body>
</html>
