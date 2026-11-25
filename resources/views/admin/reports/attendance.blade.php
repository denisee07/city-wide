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

    <section class="card" style="padding: 1.5rem;">
        <div class="table-wrap">
            <table class="table" style="width: 100%;">
                <thead><tr><th>Resident</th><th>Barangay</th><th>Event</th><th>Reference</th><th>Status</th><th>Attended</th></tr></thead>
                <tbody>
                    @forelse ($items as $r)
                        <tr>
                            <td>{{ $r->resident?->name }}</td>
                            <td>{{ $r->resident?->barangay?->name }}</td>
                            <td>{{ $r->event?->title }}</td>
                            <td>{{ $r->reference_no }}</td>
                            <td>{{ $r->status }}</td>
                            <td>{{ $r->attended_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center" style="padding: 3rem 1.5rem; color: var(--dim);">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;" class="small dim">
            <span>Total: {{ $items->count() }} records</span>
            <span>Generated {{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </section>
@endsection