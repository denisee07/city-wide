@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Events — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>Event management</h1></div>
        <div class="row">
            <a href="{{ route('admin.events.scan') }}" class="btn btn-info">Scan attendance</a>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">New event</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><div class="label">Events</div><div class="value">{{ $stats['events'] }}</div></div>
        <div class="stat"><div class="label">Registrations</div><div class="value">{{ $stats['registrations'] }}</div></div>
        <div class="stat"><div class="label">Attended</div><div class="value">{{ $stats['attended'] }}</div></div>
    </div>

    <section class="card">
        @if ($events->isEmpty())
            <div class="empty"><h3>No events</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Date</th><th>Location</th><th>Barangay</th><th>Status</th><th>Regs</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($events as $e)
                            <tr>
                                <td>{{ $e->title }}</td>
                                <td>{{ $e->event_date->format('M j, Y') }}</td>
                                <td>{{ $e->location ?? '—' }}</td>
                                <td>{{ $e->barangay?->name ?? 'Citywide' }}</td>
                                <td>{{ ucfirst($e->status) }}</td>
                                <td>{{ $e->registrations()->count() }}</td>
                                <td><a href="{{ route('admin.events.show', $e) }}" class="btn btn-sm btn-info">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $events->links() }}</div>
        @endif
    </section>
@endsection