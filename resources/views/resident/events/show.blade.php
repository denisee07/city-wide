@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', $event->title.' — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>{{ $event->title }}</h1><p class="sub">{{ $event->event_date->format('M j, Y') }} · {{ $event->location }}</p></div>
        <a href="{{ route('resident.events.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card">
        <p>{{ $event->description }}</p>
        <div class="kv" style="margin-top:1rem">
            <div><span class="k">Date</span><span class="v">{{ $event->event_date->format('M j, Y') }}</span></div>
            <div><span class="k">Time</span><span class="v">{{ $event->start_time }} - {{ $event->end_time }}</span></div>
            <div><span class="k">Location</span><span class="v">{{ $event->location ?? '—' }}</span></div>
            <div><span class="k">Status</span><span class="v">{{ ucfirst($event->status) }}</span></div>
        </div>
    </section>

    <section class="card" style="margin-top:1rem">
        <h3>Your registration</h3>
        @if (!$registration)
            <form method="POST" action="{{ route('resident.events.register', $event) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Register for this event</button>
            </form>
        @else
            <div class="kv">
                <div><span class="k">Reference</span><span class="v">{{ $registration->reference_no }}</span></div>
                <div><span class="k">Status</span><span class="v">{{ $registration->status }}</span></div>
                @if ($registration->attended_at)<div><span class="k">Attended</span><span class="v">{{ $registration->attended_at->format('M j, Y g:i A') }}</span></div>@endif
            </div>
            <div style="margin-top:1rem">
                <h4>Your QR code</h4>
                <code style="display:block;padding:.75rem;background:#000;border-radius:6px;word-break:break-all">{{ $registration->qr_code }}</code>
            </div>
        @endif
    </section>
@endsection