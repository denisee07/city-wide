@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Book an Appointment — '.config('app.name'))
@section('topbar-title', 'Book an Appointment')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Reserve a slot</span>
            <h1>Book an appointment</h1>
            <p class="sub">Pick an office, a date and a one-hour slot — we will confirm it before your visit.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.appointments.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to appointments
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <form class="card" method="POST" action="{{ route('resident.appointments.store') }}">
            @csrf

            <div class="card-head">
                <h2 class="card-title"><x-icon name="calendar" /> Booking details</h2>
                <span class="badge badge-cyan">Awaiting confirmation</span>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field span-2">
                        <label class="label" for="subject">Subject <span class="req">*</span></label>
                        <input class="input" id="subject" name="subject" maxlength="150"
                               value="{{ old('subject') }}" placeholder="e.g. Barangay ID application, Medical check-up, SK scholarship inquiry">
                        @error('subject')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="office">Office <span class="req">*</span></label>
                        <select class="select" id="office" name="office">
                            <option value="">Choose an office…</option>
                            @foreach ($offices as $key => $label)
                                <option value="{{ $key }}" @selected(old('office') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('office')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="appointment_date">Date <span class="req">*</span></label>
                        <input class="input" id="appointment_date" name="appointment_date" type="date"
                               min="{{ $today }}" value="{{ old('appointment_date') }}">
                        <div class="help">Today or any future working day.</div>
                        @error('appointment_date')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="time_slot">Time slot <span class="req">*</span></label>
                        <select class="select" id="time_slot" name="time_slot">
                            <option value="">Choose a one-hour slot…</option>
                            @foreach ($slots as $slot)
                                <option value="{{ $slot }}" @selected(old('time_slot') === $slot)>{{ $slot }}</option>
                            @endforeach
                        </select>
                        <div class="help">Slots run 8:00–11:00 AM and 1:00–4:00 PM, Monday to Friday.</div>
                        @error('time_slot')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="purpose">Purpose <span class="req">*</span></label>
                        <textarea class="textarea" id="purpose" name="purpose" rows="4" maxlength="300"
                                  placeholder="What do you need to accomplish during the visit? Add any requirement you want prepared in advance.">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <x-icon name="info" size="16" />
                    <div>
                        <strong>One booking per day.</strong>
                        <p class="small mb-0">You may hold a single pending or confirmed appointment per date. Cancel first if you need to reschedule.</p>
                    </div>
                </div>
            </div>

            <div class="card-foot">
                <div class="row between">
                    <span class="tiny dim">You will receive an APT reference number after booking.</span>
                    <div class="row">
                        <button class="btn btn-primary" type="submit">
                            <x-icon name="send" /> Book appointment
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="building" /> Offices &amp; coverage</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Barangay Hall</dt><dd>IDs, clearances, records</dd></div>
                        <div class="d"><dt>Health Center</dt><dd>Check-ups, immunization</dd></div>
                        <div class="d"><dt>Peace &amp; Order Desk</dt><dd>Complaints, blotter, incidents</dd></div>
                        <div class="d"><dt>SK Office</dt><dd>Youth programs, scholarships</dd></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clock" /> Before you come in</h2>
                </div>
                <div class="card-body">
                    <div class="stack-sm">
                        <div class="queue-row"><span class="qn">1</span><span class="grow">Wait for the confirmation status on your appointment list.</span></div>
                        <div class="queue-row"><span class="qn">2</span><span class="grow">Arrive 10 minutes early with a valid ID.</span></div>
                        <div class="queue-row"><span class="qn">3</span><span class="grow">Take a queue number at the lobby kiosk if you arrive ahead of time.</span></div>
                        <div class="queue-row"><span class="qn">4</span><span class="grow">Running late? Cancel and rebook so others can use the slot.</span></div>
                    </div>
                    <a href="{{ route('resident.queue.index') }}" class="btn btn-block mt-2">
                        <x-icon name="ticket" /> Open queue board
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
