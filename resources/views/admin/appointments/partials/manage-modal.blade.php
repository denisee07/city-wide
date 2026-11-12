{{--
    Update-booking modal, rendered per appointment.

    Same reason as the accounts board: this form used to be one shared modal
    whose action, title, status and remarks were filled in by JavaScript when
    "Manage" was clicked. Without JavaScript the form submitted a PATCH to the
    appointments index route, which is GET-only, so the officer got a 405
    instead of a saved booking. Now every field the server knows about is
    already in the HTML, and an ajax save re-renders this same slot.
--}}
@php
    $fieldId = 'manage-appt-' . $booking->id;
@endphp

<div class="modal" id="{{ $fieldId }}">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <div class="eyebrow">Appointment</div>
                <h3 class="mb-0">{{ $booking->subject }}</h3>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close">&times;</button>
        </div>

        <form method="POST" data-ajax action="{{ route('admin.appointments.update', $booking) }}">
            @csrf
            @method('PATCH')

            <div class="modal-body stack">
                <div class="alert alert-info mb-0">
                    <x-icon name="info" size="17" />
                    <div class="small">
                        {{ $booking->reference_no }} ·
                        {{ $booking->appointment_date?->format('D, M j Y') }} {{ $booking->time_slot }}
                        — choose a status and add remarks if needed.
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="appt-status-{{ $booking->id }}">Status <span class="req">*</span></label>
                    <select id="appt-status-{{ $booking->id }}" name="status" class="select">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($booking->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label class="label" for="appt-remarks-{{ $booking->id }}">Remarks</label>
                    <textarea id="appt-remarks-{{ $booking->id }}" name="remarks" class="textarea" rows="3"
                        placeholder="Confirmation details, reason for cancellation, notes for the staff…">{{ $booking->remarks }}</textarea>
                    @error('remarks')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" size="15" /> Save booking
                </button>
            </div>
        </form>
    </div>
</div>
