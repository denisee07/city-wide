{{--
    Manage-account modal, one per row.

    It used to be a single shared modal whose <form action>, title and select
    values were rewritten by JavaScript when the Manage button was clicked. That
    worked, but the no-JS path submitted a PATCH to the accounts *index* route,
    which does not accept PATCH, so the request died with a 405. Rendering the
    form per account makes the action, the method and the current role and
    status correct in the HTML the server sent, so the form is valid before any
    script has run.
--}}
@php
    $formRole = $user->isOfficial() ? 'city_admin' : 'resident';
    $fieldId = 'manage-user-' . $user->id;
@endphp

<div class="modal" id="{{ $fieldId }}">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Account</div>
                <h3 class="mb-0">{{ $user->name }}</h3>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close">&times;</button>
        </div>

        <form method="POST" data-ajax action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PATCH')

            <div class="modal-body stack">
                <div class="alert alert-warning">
                    <x-icon name="shield" size="17" />
                    <div class="small mb-0">
                        Role decides which interface this account opens. Suspending signs the
                        user out and blocks the next login.
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="role-{{ $user->id }}">Role <span class="req">*</span></label>
                    <select id="role-{{ $user->id }}" name="role" class="select">
                        @foreach ($roleLabels as $key => $label)
                            <option value="{{ $key }}" @selected($formRole === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="help">A resident uses the public portal. A City Administrator uses the console for the whole city.</div>
                    @error('role')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label class="label" for="status-{{ $user->id }}">Status <span class="req">*</span></label>
                    <select id="status-{{ $user->id }}" name="status" class="select">
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}" @selected($user->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="help">
                        {{ $user->email }}
                        @if ($user->barangay) · {{ $user->barangay->name }} @endif
                    </div>
                    @error('status')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" size="15" /> Save account
                </button>
            </div>
        </form>
    </div>
</div>
