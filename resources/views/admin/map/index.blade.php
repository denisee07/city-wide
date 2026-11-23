@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Complaint map — '.config('app.name'))
@section('topbar-title', 'Complaint map')

@section('content')
    @php
        $statusLabels = \App\Http\Controllers\Admin\ComplaintController::STATUSES;
        $colors = [
            'received' => '#22d3ee',
            'in_progress' => '#fbbf24',
            'on_hold' => '#8b5cf6',
            'resolved' => '#34d399',
            'closed' => '#fb7185',
        ];
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Situational awareness</span>
            <h1>Complaint map</h1>
            <p class="sub">Every reported incident pinned across the barangay, refreshed live.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.complaints.index', \App\Support\BarangayFilter::params()) }}" class="btn btn-ghost">
                <x-icon name="alert" size="15" /> Complaints list
            </a>
            <span class="badge badge-cyan">{{ $plotted }} plotted</span>
            @if ($unplotted > 0)
                <span class="badge badge-neutral">{{ $unplotted }} without coordinates</span>
            @endif
        </div>
    </div>

    {{-- ============ filters ============ --}}
    <div class="toolbar">
        <button type="button" class="chip active" data-map-filter="all">
            All statuses <span class="tiny dim">{{ $total }}</span>
        </button>
        @foreach ($statusLabels as $key => $label)
            <button type="button" class="chip" data-map-filter="{{ $key }}">
                {{ $label }} <span class="tiny dim">{{ $statusCounts[$key] ?? 0 }}</span>
            </button>
        @endforeach

        <span class="grow"></span>
        <span class="tiny dim">Auto-refreshes every 15 seconds</span>
    </div>

    <div class="grid grid-23">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="map" /> Live incident map</h2>
                <span class="badge badge-cyan" id="mapShown">{{ $plotted }} marker(s)</span>
            </div>
            <div class="card-body">
                <div id="adminMap" class="map-wrap"></div>

                <div class="map-legend">
                    @foreach ($colors as $key => $color)
                        <span><i style="background:{{ $color }}"></i>{{ $statusLabels[$key] }}</span>
                    @endforeach
                    <span><i style="background:#60a5fa"></i>Barangay reference points</span>
                </div>
            </div>
        </section>

        <div class="stack">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="chart" /> Distribution</h2>
                </div>
                <div class="card-body">
                    <div class="bars">
                        @foreach ($statusLabels as $key => $label)
                            <div class="bar-row">
                                <span class="lbl">{{ $label }}</span>
                                <div class="progress">
                                    <i style="width: {{ $total > 0 ? max(4, (int) round(($statusCounts[$key] ?? 0) / max(1, $total) * 100)) : 0 }}%"></i>
                                </div>
                                <span class="val">{{ $statusCounts[$key] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> How to read it</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Circle colour</dt><dd>Current case status</dd></div>
                        <div class="d"><dt>Click a pin</dt><dd>Title, category &amp; reference</dd></div>
                        <div class="d"><dt>Blue dots</dt><dd>Barangay facilities &amp; landmarks</dd></div>
                        <div class="d"><dt>Without pins</dt><dd>{{ $unplotted }} report(s) filed without coordinates</dd></div>
                    </div>

                    <a href="{{ route('admin.complaints.index') }}" class="btn btn-ghost btn-block btn-sm mt-2">
                        <x-icon name="alert" size="15" /> Open the complaints queue
                    </a>
                </div>
            </section>
        </div>
    </div>

    @push('styles')<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">@endpush
    @push('scripts')<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>@endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('adminMap');
                if (!el || typeof L === 'undefined') return;

                var colors = {
                    received: '#22d3ee',
                    in_progress: '#fbbf24',
                    on_hold: '#8b5cf6',
                    resolved: '#34d399',
                    closed: '#fb7185'
                };

                /* MapController supplies the centre: the official's barangay, or the
                   whole city for the city office. Do not hardcode a location here. */
                var CENTER = [{{ $latitude }}, {{ $longitude }}];
                var map = L.map(el).setView(CENTER, {{ (int) $zoom }});
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                var complaints = L.layerGroup().addTo(map);
                var points = L.layerGroup().addTo(map);
                var all = [];
                var filter = 'all';

                function esc(value) {
                    return String(value === null || value === undefined ? '' : value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');
                }

                function render() {
                    complaints.clearLayers();
                    var shown = 0;

                    all.forEach(function (c) {
                        if (filter !== 'all' && c.status !== filter) return;
                        if (c.latitude === null || c.latitude === undefined) return;
                        if (c.longitude === null || c.longitude === undefined) return;

                        var color = colors[c.status] || '#22d3ee';
                        shown++;

                        L.circleMarker([c.latitude, c.longitude], {
                            radius: 9,
                            color: color,
                            weight: 2,
                            fillColor: color,
                            fillOpacity: .35
                        })
                            .addTo(complaints)
                            .bindPopup(
                                '<strong>' + esc(c.title) + '</strong><br>' +
                                '<span style="color:#93a1bd">' + esc(c.reference_no) + ' · ' + esc(c.category) + '</span><br>' +
                                '<span style="color:#93a1bd">' + esc(c.location) + '</span>'
                            );
                    });

                    var badge = document.getElementById('mapShown');
                    if (badge) badge.textContent = shown + ' marker(s)';
                }

                document.querySelectorAll('[data-map-filter]').forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        document.querySelectorAll('[data-map-filter]').forEach(function (c) {
                            c.classList.remove('active');
                        });
                        chip.classList.add('active');
                        filter = chip.getAttribute('data-map-filter');
                        render();
                    });
                });

                window.pollUrl('{{ url('/api/map/complaints') }}', function (data) {
                    all = Array.isArray(data) ? data : [];
                    render();
                }, 15000);

                window.pollUrl('{{ url('/api/map/points') }}', function (data) {
                    points.clearLayers();
                    (Array.isArray(data) ? data : []).forEach(function (p) {
                        if (p.latitude === null || p.latitude === undefined) return;
                        if (p.longitude === null || p.longitude === undefined) return;

                        L.circleMarker([p.latitude, p.longitude], {
                            radius: 5,
                            color: '#60a5fa',
                            weight: 2,
                            fillColor: '#60a5fa',
                            fillOpacity: .6
                        }).addTo(points).bindTooltip(p.label || 'Barangay point');
                    });
                }, 30000);
            });
        </script>
    @endpush
@endsection
