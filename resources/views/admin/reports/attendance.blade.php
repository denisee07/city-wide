@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Attendance — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Event attendance</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <form method="GET" action="{{ route('admin.reports.attendance') }}" class="toolbar">
        <select name="event" class="input">
            <option value="">All events</option>
            @foreach ($events as $e)
                <option value="{{ $e->id }}" @selected(optional($event)->id===$e->id)>{{ $e->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Resident</th><th>Barangay</th><th>Event</th><th>Reference</th><th>Status</th><th>Attended</th></tr></thead>
                <tbody>
                    @foreach ($items as $r)
                        <tr>
                            <td>{{ $r->resident?->name }}</td>
                            <td>{{ $r->resident?->barangay?->name }}</td>
                            <td>{{ $r->event?->title }}</td>
                            <td>{{ $r->reference_no }}</td>
                            <td>{{ $r->status }}</td>
                            <td>{{ $r->attended_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small dim" style="margin-top:1rem">Total: {{ $items->count() }}. Generated {{ now()->format('M j, Y g:i A') }}.</p>
    </section>
@endsection