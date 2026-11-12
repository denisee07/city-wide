@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'My Profile — '.config('app.name'))
@section('topbar-title', 'My Profile')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Account settings</span>
            <h1>My profile</h1>
            <p class="sub">Keep your contact details current so the barangay can reach you about requests and appointments.</p>
        </div>
        <div class="row">
            <span class="badge badge-green"><x-icon name="shield" size="13" /> Resident account</span>
        </div>
    </div>

    <form class="grid grid-23" method="POST" action="{{ route('resident.profile.update') }}">
        @csrf
        @method('PUT')

        {{-- ============ left: personal details ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="user" /> Personal details</h2>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="label" for="name">Full name <span class="req">*</span></label>
                            <input class="input" id="name" name="name" maxlength="120"
                                   value="{{ old('name', $user->name) }}">
                            @error('name')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="phone">Mobile number</label>
                            <input class="input" id="phone" name="phone" maxlength="30"
                                   value="{{ old('phone', $user->phone) }}" placeholder="09XX XXX XXXX">
                            @error('phone')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="birth_date">Birth date</label>
                            <input class="input" id="birth_date" name="birth_date" type="date"
                                   max="{{ now()->toDateString() }}"
                                   value="{{ old('birth_date', $user->birth_date?->toDateString()) }}">
                            @error('birth_date')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="purok">Purok</label>
                            <input class="input" id="purok" name="purok" maxlength="80"
                                   value="{{ old('purok', $user->purok) }}" placeholder="e.g. Purok 3 — Sampaguita">
                            @error('purok')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="address">Street address</label>
                            <input class="input" id="address" name="address" maxlength="190"
                                   value="{{ old('address', $user->address) }}" placeholder="House no., street">
                            @error('address')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="bio">Short bio</label>
                            <textarea class="textarea" id="bio" name="bio" rows="3" maxlength="500"
                                      placeholder="A line about yourself — helps officials recognise you at the counter.">{{ old('bio', $user->bio) }}</textarea>
                            <div class="help">Optional, up to 500 characters.</div>
                            @error('bio')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <x-icon name="info" size="16" />
                        <div>
                            <strong>Account role is fixed.</strong>
                            <p class="small mb-0">Resident accounts cannot be upgraded here — visit the barangay hall for role changes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="lock" /> Change password <span class="dim">(optional)</span></h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="label" for="password">New password</label>
                            <div class="pw-wrap">
                                <input class="input" id="password" name="password" type="password"
                                       minlength="8" maxlength="64"
                                       autocomplete="new-password" placeholder="Leave blank to keep current"
                                       aria-describedby="profile-pw-meter-hint profile-pw-meter-rules">
                                @include('partials.password-toggle', ['for' => 'password', 'what' => 'new password'])
                            </div>

                            {{-- Same policy and meter as registration - App\Support\PasswordPolicy --}}
                            @include('partials.password-strength', [
                                'pwId'      => 'password',
                                'confirmId' => 'password_confirmation',
                                'matchId'   => 'pw-match',
                                'meterId'   => 'profile-pw-meter',
                                'email'     => $user->email,
                                'optional'  => true,
                                'blankHint' => 'Leave blank to keep your current password',
                            ])

                            @foreach ($errors->get('password') as $message)
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @endforeach
                        </div>

                        <div class="field span-2">
                            <label class="label" for="password_confirmation">Confirm new password</label>
                            <div class="pw-wrap">
                                <input class="input" id="password_confirmation" name="password_confirmation" type="password"
                                       minlength="8" maxlength="64"
                                       autocomplete="new-password" placeholder="Repeat the new password"
                                       aria-describedby="pw-match">
                                @include('partials.password-toggle', ['for' => 'password_confirmation', 'what' => 'password confirmation'])
                            </div>
                            <div class="pw-match" id="pw-match" role="status" hidden></div>
                            @error('password_confirmation')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-foot">
                    <div class="row between">
                        <span class="tiny dim">Saved changes apply immediately.</span>
                        <div class="row">
                            <a href="{{ route('resident.dashboard') }}" class="btn btn-ghost">Cancel</a>
                            <button class="btn btn-primary" type="submit">
                                <x-icon name="check" /> Save changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ right: account card ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="award" /> Account</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <span class="avatar lg">{{ $user->initials }}</span>
                        <div>
                            <div class="bold">{{ $user->name }}</div>
                            <div class="small dim">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="detail-list">
                        <div class="d"><dt>Role</dt><dd><span class="badge badge-cyan">{{ $user->role_label }}</span></dd></div>
                        <div class="d"><dt>Status</dt><dd><span class="badge badge-green">{{ $user->status }}</span></dd></div>
                        <div class="d"><dt>Member since</dt><dd>{{ $user->created_at?->format('M d, Y') ?? '—' }}</dd></div>
                        <div class="d"><dt>Purok</dt><dd>{{ $user->purok ?: 'Not set' }}</dd></div>
                        <div class="d"><dt>Mobile</dt><dd>{{ $user->phone ?: 'Not set' }}</dd></div>
                    </div>
                </div>
            </div>

            {{-- "My complaints / Document requests / Appointments" repeated the
                 three sidebar rows a resident is already looking at, so the
                 card went. The sidebar is the only place destinations live. --}}
        </div>
    </form>
@endsection
