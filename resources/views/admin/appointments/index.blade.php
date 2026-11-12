@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Appointments — '.config('app.name'))
@section('topbar-title', 'Appointments')

@section('content')
    @php($currentStatus = request('status'))

    <div class="page-head">
        <div>
            <span class="eyebrow">Scheduling</span>
            <h1>Appointments</h1>
            <p class="sub">Resident bookings grouped by date — confirm, complete or reschedule from here.</p>
        </div>
        <div class="row">
            <span class="badge badge-amber">{{ $counts['pending'] ?? 0 }} awaiting confirmation</span>
        </div>
    </div>

    {{-- ============ status tabs ============ --}}
    {{-- ListFilters keeps the search box, the barangay drill-down and any other
         choice intact when a tab is pressed; it used to reset them to "All". --}}
    <div class="tabs">
        <a href="{{ route('admin.appointments.index', \App\Support\ListFilters::with('status', null)) }}" class="tab {{ ! $currentStatus ? 'active' : '' }}">
            All <span class="count">{{ $counts->sum() }}</span>
        </a>
        @foreach ($statuses as $key => $label)
            <a href="{{ route('admin.appointments.index', \App\Support\ListFilters::with('status', $key)) }}" class="tab {{ $currentStatus === $key ? 'active' : '' }}">
                {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ search ============ --}}
    <form method="GET" data-filter-ajax action="{{ route('admin.appointments.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search reference, subject, office or resident…"
                    value="{{ old('q', request('q')) }}">
            </div>

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>

            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    {{-- ============ grouped by date ============ --}}
    @if ($appointments->isEmpty())
        <section class="card">
            <div class="empty">
                <div class="ico"><x-icon name="calendar" size="22" /></div>
                <h3>No appointments found</h3>
                <p>Nothing booked for this filter. Resident bookings appear here as soon as they are made.</p>
            </div>
        </section>
    @else
        <div class="stack">
            @foreach ($appointments as $date => $bookings)
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title">
                            <x-icon name="calendar" />
                            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                            @if (\Carbon\Carbon::parse($date)->isToday())
                                <span class="badge badge-cyan">Today</span>
                            @elseif (\Carbon\Carbon::parse($date)->isTomorrow())
                                <span class="badge badge-blue">Tomorrow</span>
                            @endif
                        </h2>
                        <span class="badge badge-neutral">{{ $bookings->count() }} booking(s)</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Slot</th>
                                    <th>Reference</th>
                                    <th>Subject</th>
                                    <th>Resident</th>
                                    <th>Status</th>
                                    <th>Processed by</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr data-record-id="{{ $booking->id }}">
                                        <td class="mono nowrap">{{ $booking->time_slot }}</td>
                                        <td class="mono tiny nowrap">{{ $booking->reference_no }}</td>
                                        <td class="strong">
                                            {{ $booking->subject }}
                                            <div class="tiny dim">{{ $booking->office }} · {{ \Illuminate\Support\Str::limit($booking->purpose, 60) }}</div>
                                        </td>
                                        <td class="nowrap">
                                            {{ $booking->resident?->name ?? 'Resident' }}
                                            <div class="tiny dim">{{ $booking->resident?->purok ?: '—' }}</div>
                                        </td>
                                        {{-- Rewritten in place after an ajax save; see app.js. --}}
                                        <td data-status-cell>
                                            @include('admin.partials.status-cell', [
                                                'badge' => $badge[$booking->status] ?? 'badge-neutral',
                                                'label' => $booking->statusLabel(),
                                                'remark' => $booking->remarks,
                                            ])
                                        </td>
                                        <td class="nowrap tiny dim">{{ $booking->processor?->name ?? '—' }}</td>
                                        <td class="right nowrap">
                                            <button type="button" class="btn btn-sm btn-ghost"
                                                data-modal-open="manage-appt-{{ $booking->id }}">
                                                <x-icon name="sliders" size="14" /> Manage
                                            </button>

                                            {{-- Server-rendered form for this booking, so
                                                 its action and current values are correct
                                                 before any script has run. --}}
                                            <div data-manage-slot="{{ $booking->id }}">
                                                @include('admin.appointments.partials.manage-modal', [
                                                    'booking' => $booking,
                                                    'statuses' => $statuses,
                                                ])
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>
    @endif

@endsection
