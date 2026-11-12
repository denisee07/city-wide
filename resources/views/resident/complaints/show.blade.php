@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', $complaint->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Complaint Tracker')

@php($badge = fn (string $s) => match ($s) {
    'received', 'pending', 'waiting' => 'badge-cyan',
    'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber',
    'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green',
    'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose',
    'on_hold', 'claimed' => 'badge-violet',
    default => 'badge-neutral',
})
@php($priorityBadge = fn (string $p) => match ($p) {
    'urgent' => 'badge-rose',
    'high' => 'badge-amber',
    'medium' => 'badge-blue',
    default => 'badge-neutral',
})
@php($stepsOn = (int) match ($complaint->status) {
    'in_progress', 'on_hold' => 2,
    'resolved' => 3,
    'closed' => 4,
    default => 1,
})
@php($hasPins = $complaint->latitude !== null && $complaint->longitude !== null)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Complaint tracker</span>
            <h1>{{ $complaint->title }}</h1>
            <p class="sub">
                <span class="mono">{{ $complaint->reference_no }}</span>
                &middot; Filed {{ $complaint->created_at->format('M d, Y \a\t h:i A') }}
            </p>
        </div>
        <div class="row">
            <a href="{{ route('resident.complaints.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> All complaints
            </a>
            <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> New complaint
            </a>
        </div>
    </div>

    {{-- ============ status header ============ --}}
    <div class="card">
        <div class="card-body">
            <div class="row between">
                <div class="row">
                    <span class="badge {{ $badge($complaint->status) }}">
                        {{ $statuses[$complaint->status] ?? ucfirst($complaint->status) }}
                    </span>
                    <span class="badge {{ $priorityBadge($complaint->priority) }}">{{ ucfirst($complaint->priority) }} priority</span>
                    @if ($complaint->resolved_at)
                        <span class="badge badge-green">Closed out {{ $complaint->resolved_at->format('M d, Y') }}</span>
                    @endif
                </div>
                <button class="btn btn-sm" data-copy="{{ $complaint->reference_no }}">
                    <x-icon name="clipboard" size="14" /> Copy reference
                </button>
            </div>

            <div class="steps mt-3">
                @for ($i = 1; $i <= 4; $i++)
                    <span class="st {{ $i <= $stepsOn ? 'on' : '' }}"></span>
                @endfor
            </div>

            <div class="row between mt-1">
                <span class="tiny {{ $stepsOn >= 1 ? 'muted' : 'dim' }}">1 · Received</span>
                <span class="tiny {{ $stepsOn >= 2 ? 'muted' : 'dim' }}">2 · In progress</span>
                <span class="tiny {{ $stepsOn >= 3 ? 'muted' : 'dim' }}">3 · Resolved</span>
                <span class="tiny {{ $stepsOn >= 4 ? 'muted' : 'dim' }}">4 · Closed</span>
            </div>
        </div>
    </div>

    <div class="grid grid-23 mt-3">
        {{-- ============ left: report + timeline ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="flag" /> What was reported</h2>
                    <span class="badge badge-blue">{{ $categories[$complaint->category] ?? ucfirst($complaint->category) }}</span>
                </div>
                <div class="card-body">
                    <p class="muted mb-0">{!! nl2br(e($complaint->description)) !!}</p>

                    <hr class="divider">

                    <div class="detail-list">
                        <div class="d"><dt>Location</dt><dd>{{ $complaint->location }}</dd></div>
                        @if ($complaint->purok)
                            <div class="d"><dt>Purok</dt><dd>{{ $complaint->purok }}</dd></div>
                        @endif
                        <div class="d"><dt>Filed by</dt><dd>{{ $complaint->resident?->name ?? auth()->user()->name }}</dd></div>
                        <div class="d"><dt>Date filed</dt><dd>{{ $complaint->created_at->format('M d, Y · h:i A') }}</dd></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clock" /> Progress timeline</h2>
                    <span class="badge badge-neutral">{{ $updates->count() }} update{{ $updates->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if ($updates->isEmpty())
                        <div class="empty">
                            <div class="ico"><x-icon name="clock" size="26" /></div>
                            <h3>No updates yet</h3>
                            <p>Status changes and notes from the barangay action officer will appear here.</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach ($updates as $update)
                                @php($dot = match (true) {
                                    in_array($update->status, ['closed', 'rejected'], true) => 'reject',
                                    in_array($update->status, ['resolved', 'done', 'released'], true) => 'done',
                                    in_array($update->status, ['in_progress', 'under_review', 'on_hold'], true) => 'active',
                                    default => '',
                                })
                                @php($dot = $loop->first && $dot === '' && in_array($complaint->status, ['received', 'in_progress', 'on_hold'], true) ? 'active' : $dot)

                                <div class="timeline-item {{ $dot }}">
                                    <div class="timeline-head">
                                        <span class="timeline-title">{{ $update->title }}</span>
                                        <span class="badge {{ $badge($update->status) }}">
                                            {{ $statuses[$update->status] ?? ucfirst(str_replace('_', ' ', $update->status)) }}
                                        </span>
                                    </div>

                                    @if ($update->note)
                                        <div class="timeline-note">{{ $update->note }}</div>
                                    @endif

                                    <div class="timeline-time mt-1">
                                        {{ $update->created_at->format('M d, Y · h:i A') }}
                                        &middot; {{ $update->author?->name ?? 'Barangay desk' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ right: facts, remarks, map ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="sliders" /> At a glance</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Status</dt><dd><span class="badge {{ $badge($complaint->status) }}">{{ $statuses[$complaint->status] ?? ucfirst($complaint->status) }}</span></dd></div>
                        <div class="d"><dt>Priority</dt><dd><span class="badge {{ $priorityBadge($complaint->priority) }}">{{ ucfirst($complaint->priority) }}</span></dd></div>
                        <div class="d"><dt>Category</dt><dd>{{ $categories[$complaint->category] ?? ucfirst($complaint->category) }}</dd></div>
                        <div class="d"><dt>Action officer</dt><dd>{{ $complaint->assignee?->name ?? 'Not yet assigned' }}</dd></div>
                        <div class="d"><dt>Last update</dt><dd>{{ $updates->first()?->created_at?->diffForHumans() ?? $complaint->created_at->diffForHumans() }}</dd></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="chat" /> Barangay remarks</h2>
                </div>
                <div class="card-body">
                    @if ($complaint->admin_remarks)
                        <p class="muted mb-0">{{ $complaint->admin_remarks }}</p>
                    @else
                        <div class="alert alert-info mb-0">
                            <x-icon name="info" size="16" />
                            <div>
                                <strong>No remarks yet.</strong>
                                <p class="small mb-0">Notes from the barangay captain or action officer will be posted here.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($hasPins)
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="pin" /> Reported location</h2>
                        <span class="pin-note"><x-icon name="pin" size="13" /> Pinned</span>
                    </div>
                    <div class="card-body">
                        <div class="map-wrap short" id="complaint-map"></div>
                        <div class="map-legend">
                            <span><i style="background:#22d3ee"></i> Complaint pin</span>
                            <span><i style="background:#8b5cf6"></i> Barangay centre</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var host = document.getElementById('complaint-map');
        if (!host || typeof L === 'undefined') return;

        var data = {
            lat: @json($complaint->latitude),
            lng: @json($complaint->longitude),
            title: @json($complaint->title),
            ref: @json($complaint->reference_no),
            location: @json($complaint->location),
            status: @json($statuses[$complaint->status] ?? $complaint->status)
        };

        /* Open on the incident itself. A complaint filed without a pin falls
           back to the Santa Rosa city centre rather than somewhere unrelated. */
        var hasCoords = data.lat != null && data.lng != null
            && isFinite(Number(data.lat)) && isFinite(Number(data.lng));
        var START = hasCoords
            ? [Number(data.lat), Number(data.lng)]
            : [{{ \App\Models\Barangay::CITY_CENTER[0] }}, {{ \App\Models\Barangay::CITY_CENTER[1] }}];

        var map = L.map(host).setView(START, hasCoords ? 17 : {{ \App\Models\Barangay::CITY_ZOOM }});

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (hasCoords) {
            L.circleMarker([Number(data.lat), Number(data.lng)], {
                radius: 9,
                color: '#22d3ee',
                weight: 3,
                fillColor: '#22d3ee',
                fillOpacity: 0.35
            }).addTo(map).bindPopup(
                '<strong>' + data.title + '</strong><br>' +
                '<span class="mono dim">' + data.ref + '</span><br>' +
                data.location + '<br><span class="muted">' + data.status + '</span>'
            );
        }

        setTimeout(function () { map.invalidateSize(); }, 260);
    });
</script>
@endpush
