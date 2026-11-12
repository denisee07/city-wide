@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'File a Complaint — '.config('app.name'))
@section('topbar-title', 'File a Complaint')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">New report</span>
            <h1>File a complaint</h1>
            <p class="sub">Describe the concern, drop a pin, and the barangay will take it from there.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.complaints.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to complaints
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <form class="card" method="POST" action="{{ route('resident.complaints.store') }}">
            @csrf

            <div class="card-head">
                <h2 class="card-title"><x-icon name="edit" /> Complaint details</h2>
                <span class="badge badge-cyan">Draft</span>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field span-2">
                        <label class="label" for="title">Title <span class="req">*</span></label>
                        <input class="input" id="title" name="title" maxlength="160"
                               value="{{ old('title') }}" placeholder="e.g. Uncollected garbage along Mabini Street">
                        @error('title')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="category">Category <span class="req">*</span></label>
                        <select class="select" id="category" name="category">
                            <option value="">Select a category…</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="priority">Priority <span class="req">*</span></label>
                        <select class="select" id="priority" name="priority">
                            <option value="">Select urgency…</option>
                            @foreach ($priorities as $key => $label)
                                <option value="{{ $key }}" @selected(old('priority') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="help">Urgent reports are escalated to the on-duty tanod immediately.</div>
                        @error('priority')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="location">Where did it happen? <span class="req">*</span></label>
                        <input class="input" id="location" name="location" maxlength="190"
                               value="{{ old('location') }}" placeholder="Street, landmark or purok — e.g. Near the covered court, Purok 3">
                        @error('location')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="description">What happened? <span class="req">*</span></label>
                        <textarea class="textarea" id="description" name="description" rows="6"
                                  placeholder="Give us the details: when it started, how often it happens, who is affected…">{{ old('description') }}</textarea>
                        <div class="help">Minimum 15 characters. Add dates and landmarks so our team can act faster.</div>
                        @error('description')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="latitude">Latitude <span class="dim">(optional)</span></label>
                        <input class="input mono" id="latitude" name="latitude" inputmode="decimal"
                               value="{{ old('latitude') }}" placeholder="14.604200">
                        @error('latitude')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="longitude">Longitude <span class="dim">(optional)</span></label>
                        <input class="input mono" id="longitude" name="longitude" inputmode="decimal"
                               value="{{ old('longitude') }}" placeholder="121.041000">
                        @error('longitude')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="button" class="btn btn-info btn-sm mt-1" id="geoBtn">
                    <x-icon name="pin" size="14" /> Use my current location
                </button>
            </div>

            <div class="card-foot">
                <div class="row between">
                    <span class="tiny dim">Required fields are marked with an asterisk.</span>
                    <div class="row">
                        <button class="btn btn-primary" type="submit">
                            <x-icon name="send" /> Submit complaint
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clock" /> What happens next</h2>
                </div>
                <div class="card-body">
                    <div class="stack-sm">
                        <div class="queue-row"><span class="qn">1</span><span class="grow">Complaint is logged and you receive a CMP reference number.</span></div>
                        <div class="queue-row"><span class="qn">2</span><span class="grow">The barangay desk assesses and assigns an action officer.</span></div>
                        <div class="queue-row"><span class="qn">3</span><span class="grow">You see every status update on your complaint tracker.</span></div>
                        <div class="queue-row"><span class="qn">4</span><span class="grow">Resolution notes and remarks are posted for your review.</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> Priority guide</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt><span class="badge badge-rose">Urgent</span></dt><dd>Immediate threat to life, safety or property</dd></div>
                        <div class="d"><dt><span class="badge badge-amber">High</span></dt><dd>Affecting many households right now</dd></div>
                        <div class="d"><dt><span class="badge badge-blue">Medium</span></dt><dd>Recurring issue that needs scheduling</dd></div>
                        <div class="d"><dt><span class="badge badge-neutral">Low</span></dt><dd>Minor concern, can wait for regular action</dd></div>
                    </div>
                    <hr class="divider">
                    <div class="pin-note"><x-icon name="info" size="14" /> For crimes in progress call 911 or the barangay hotline first.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('geoBtn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                window.toast && window.toast('Your browser does not support location capture.', 'error');
                return;
            }

            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = document.getElementById('latitude');
                var lng = document.getElementById('longitude');
                if (lat) lat.value = pos.coords.latitude.toFixed(6);
                if (lng) lng.value = pos.coords.longitude.toFixed(6);
                btn.disabled = false;
                window.toast && window.toast('Coordinates captured from your device.', 'success');
            }, function () {
                btn.disabled = false;
                window.toast && window.toast('Location unavailable — please type the coordinates manually.', 'error');
            }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 });
        });
    });
</script>
@endpush
