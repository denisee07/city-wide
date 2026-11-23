@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'Events — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>Upcoming events</h1></div>
    </div>

    <section class="card">
        @if ($events->isEmpty())
            <div class="empty"><h3>No events</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Date</th><th>Location</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($events as $e)
                            <tr>
                                <td>{{ $e->title }}</td>
                                <td>{{ $e->event_date->format('M j, Y') }}</td>
                                <td>{{ $e->location ?? '—' }}</td>
                                <td><a href="{{ route('resident.events.show', $e) }}" class="btn btn-sm btn-info">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $events->links() }}</div>
        @endif
    </section>
@endsection