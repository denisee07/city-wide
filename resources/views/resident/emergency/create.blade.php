@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'Report an emergency — '.config('app.name'))
@section('topbar-title', 'Emergency Assistance')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>Report an incident</h1><p class="sub">Send a report to your barangay or City Hall. For life-threatening emergencies, call emergency hotlines first.</p></div>
        <a href="{{ route('resident.emergency.index') }}" class="btn btn-ghost">My reports</a>
    </div>

    <section class="card" style="max-width:760px">
        <form method="POST" action="{{ route('resident.emergency.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label>Emergency category *</label>
                    <select name="category" class="input" required>
                        @foreach (\App\Models\EmergencyReport::categories() as $k => $v)
                            <option value="{{ $k }}" @selected(old('category')===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Send report to *</label>
                    <select name="destination" class="input" required>
                        <option value="barangay" @selected(old('destination','barangay')==='barangay')>Barangay Administration</option>
                        <option value="city" @selected(old('destination')==='city')>City Hall / CDRRMO</option>
                    </select>
                </div>
                <div class="field">
                    <label>Barangay of incident *</label>
                    <select name="barangay_id" class="input" required>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}" @selected(old('barangay_id', $user->barangay_id)==$b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Location / address *</label>
                    <input type="text" name="location" class="input" value="{{ old('location') }}" required>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Description *</label>
                    <textarea name="description" class="input" rows="4" required>{{ old('description') }}</textarea>
                </div>
                <div class="field">
                    <label>Latitude (optional)</label>
                    <input type="text" name="latitude" class="input" value="{{ old('latitude') }}">
                </div>
                <div class="field">
                    <label>Longitude (optional)</label>
                    <input type="text" name="longitude" class="input" value="{{ old('longitude') }}">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Photo (optional)</label>
                    <input type="file" name="photo" class="input" accept="image/*">
                </div>
            </div>
            <div class="row" style="margin-top:1rem">
                <button type="submit" class="btn btn-danger">Submit emergency report</button>
                <a href="{{ route('resident.emergency.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>

    <section class="card" style="max-width:760px;margin-top:1rem">
        <h3>Emergency hotlines</h3>
        <p class="tiny dim">City Hall: (049) 534-3000 · CDRRMO: 911 · Barangay Hall: see barangay page.</p>
    </section>
@endsection