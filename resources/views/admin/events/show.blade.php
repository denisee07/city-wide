@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', $event->title.' — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>{{ $event->title }}</h1><p class="sub">{{ $event->event_date->format('M j, Y') }} · {{ $event->location }}</p></div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <div class="grid-2">
        <section class="card">
            <h3>Event details</h3>
            <form method="POST" action="{{ route('admin.events.update', $event) }}">
                @csrf @method('PUT')
                <div class="field"><label>Title</label><input type="text" name="title" class="input" value="{{ $event->title }}" required></div>
                <div class="field"><label>Date</label><input type="date" name="event_date" class="input" value="{{ $event->event_date->format('Y-m-d') }}" required></div>
                <div class="field"><label>Status</label>
                    <select name="status" class="input">
                        @foreach (['upcoming','ongoing','completed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected($event->status===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Location</label><input type="text" name="location" class="input" value="{{ $event->location }}"></div>
                <div class="field"><label>Description</label><textarea name="description" class="input" rows="2">{{ $event->description }}</textarea></div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete event?')" style="margin-top:.5rem">
                @csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete event</button>
            </form>
        </section>

        <section class="card">
            <h3>Registrations ({{ $event->registrations->count() }})</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Resident</th><th>Reference</th><th>Status</th><th>QR</th></tr></thead>
                    <tbody>
                        @foreach ($event->registrations as $r)
                            <tr>
                                <td>{{ $r->resident?->name }}</td>
                                <td>{{ $r->reference_no }}</td>
                                <td>{{ $r->status }}</td>
                                <td class="tiny">{{ $r->qr_code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection