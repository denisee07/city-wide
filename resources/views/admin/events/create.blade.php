@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'New event — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>New event</h1></div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:760px">
        <form method="POST" action="{{ route('admin.events.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field" style="grid-column:1/-1"><label>Title *</label><input type="text" name="title" class="input" required></div>
                <div class="field"><label>Date *</label><input type="date" name="event_date" class="input" required></div>
                <div class="field"><label>Status</label>
                    <select name="status" class="input">
                        @foreach (['upcoming','ongoing','completed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected($s==='upcoming')>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Start time</label><input type="text" name="start_time" class="input"></div>
                <div class="field"><label>End time</label><input type="text" name="end_time" class="input"></div>
                <div class="field"><label>Location</label><input type="text" name="location" class="input"></div>
                <div class="field"><label>Barangay (blank = citywide)</label>
                    <select name="barangay_id" class="input">
                        <option value="">Citywide</option>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Registration limit</label><input type="number" name="registration_limit" class="input" min="1"></div>
                <div class="field" style="grid-column:1/-1"><label>Description</label><textarea name="description" class="input" rows="3"></textarea></div>
            </div>
            <div class="row" style="margin-top:1rem"><button type="submit" class="btn btn-primary">Create event</button></div>
        </form>
    </section>
@endsection