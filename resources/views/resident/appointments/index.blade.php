@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Appointments — '.config('app.name'))
@section('topbar-title', 'Appointments')

@php($badge = fn (string $s) => match ($s) {
    'received', 'pending', 'waiting' => 'badge-cyan',
    'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber',
    'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green',
    'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose',
    'on_hold', 'claimed' => 'badge-violet',
    default => 'badge-neutral',
})
@php($tabUrl = fn (string $s) => route('resident.appointments.index', array_filter(['status' => $s !== '' ? $s : null])))

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Schedule a visit</span>
            <h1>Appointments</h1>
            <p class="sub">Reserve a time slot with the barangay hall, health center, peace &amp; order desk or SK office.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.appointments.create') }}" class="btn btn-primary">
                <x-icon name="calendar" /> Book appointment
            </a>
        </div>
    </div>

    <div class="tabs">
        <a href="{{ $tabUrl('') }}" class="tab {{ $status === '' ? 'active' : '' }}">
            All <span class="count">{{ $counts['all'] }}</span>
        </a>
        @foreach ($statuses as $key => $label)
            <a href="{{ $tabUrl($key) }}" class="tab {{ $status === $key ? 'active' : '' }}">
                {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card">
        @if ($appointments->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="calendar" size="26" /></div>
                <h3>{{ $status !== '' ? 'No appointments with this status' : 'Your calendar is clear' }}</h3>
                <p>{{ $status !== '' ? 'Switch tabs to see the rest of your bookings.' : 'Book a slot and skip the walk-in line at the barangay hall.' }}</p>
                <a href="{{ route('resident.appointments.create') }}" class="btn btn-primary">
                    <x-icon name="calendar" /> Book an appointment
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Subject</th>
                            <th>Office</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td class="mono nowrap">{{ $appointment->reference_no }}</td>
                                <td class="strong">
                                    {{ $appointment->subject }}
                                    <div class="tiny dim">{{ \Illuminate\Support\Str::limit($appointment->purpose, 52) }}</div>
                                </td>
                                <td class="nowrap">{{ $appointment->office }}</td>
                                <td class="nowrap">
                                    <span class="mono">{{ $appointment->appointment_date->format('D, M d, Y') }}</span>
                                    <div class="tiny dim">{{ $appointment->time_slot }}</div>
                                </td>
                                <td><span class="badge {{ $badge($appointment->status) }}">{{ $appointment->statusLabel() }}</span></td>
                                <td class="right">
                                    <div class="row">
                                        <span class="grow"></span>
                                        @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                            <form method="POST" action="{{ route('resident.appointments.cancel', $appointment) }}"
                                                  data-confirm="Cancel appointment {{ $appointment->reference_no }}?">
                                                @csrf
                                                <button class="btn btn-sm btn-danger" type="submit">
                                                    <x-icon name="x" size="14" /> Cancel
                                                </button>
                                            </form>
                                        @else
                                            <span class="tiny dim">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($appointments->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $appointments->currentPage() - 2), max(1, min($appointments->lastPage(), $appointments->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($appointments->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $appointments->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $appointments->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($appointments->hasMorePages())
                <a href="{{ $appointments->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif
@endsection

