@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Barangay Map — '.config('app.name'))
@section('topbar-title', 'Barangay Map')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Situation map</span>
            <h1>Barangay map</h1>
            <p class="sub">Live pins for reported concerns and community landmarks around the barangay centre.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                <x-icon name="pin" /> Report a location
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="map" /> Community situation map</h2>
                    <span class="badge badge-cyan"><x-icon name="refresh" size="13" /> Auto-refreshing</span>
                </div>
                <div class="card-body">
                    <div class="map-wrap" id="map"></div>

                    <div class="map-legend">
                        <span><i style="background:#fb7185"></i> Urgent &amp; high priority reports</span>
                        <span><i style="background:#fbbf24"></i> Medium priority reports</span>
                        <span><i style="background:#60a5fa"></i> Low priority reports</span>
                        <span><i style="background:#8b5cf6"></i> Barangay centre</span>
                        <span><i style="background:#34d399"></i> Community point</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <x-icon name="info" size="16" />
                <div>
                    <strong>Tap a pin</strong>
                    <p class="small mb-0">Each complaint pin opens its reference number, category and current status. The list on the right stays in sync with the map.</p>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="alert" /> Nearby complaints</h2>
                    <span class="badge badge-cyan" id="pinCount">…</span>
                </div>
                <div class="card-body">
                    <div class="stack-sm" id="mapList">
                        <div class="empty">
                            <div class="ico"><x-icon name="pin" size="26" /></div>
                            <h3>Loading pins…</h3>
                            <p>Fetching the latest reports from the barangay map.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="layers" /> Map notes</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Centre</dt><dd>Santa Rosa City, Laguna</dd></div>
                        <div class="d"><dt>Zoom</dt><dd class="mono">{{ (int) $zoom }} &middot; city view</dd></div>
                        <div class="d"><dt>Refresh</dt><dd>Every 30 seconds</dd></div>
                        <div class="d"><dt>Data source</dt><dd>Resident complaints</dd></div>
                    </div>
                    <hr class="divider">
                    <a href="{{ route('resident.complaints.index') }}" class="btn btn-block">
                        <x-icon name="alert" /> Track my complaints
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<style>
    #mapList .queue-row { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var host = document.getElementById('map');
        if (!host || typeof L === 'undefined') return;

        /* Santa Rosa City centre, supplied by Resident\MapController. */
        var CENTER = [{{ $latitude }}, {{ $longitude }}];
        var ZOOM = {{ (int) $zoom }};

        var esc = function (value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        };

        var statusClass = function (status) {
            var map = {
                received: 'badge-cyan', pending: 'badge-cyan', waiting: 'badge-cyan',
                in_progress: 'badge-amber', under_review: 'badge-amber', confirmed: 'badge-amber',
                serving: 'badge-amber', called: 'badge-amber', ready_for_release: 'badge-amber',
                resolved: 'badge-green', approved: 'badge-green', done: 'badge-green', released: 'badge-green',
                closed: 'badge-rose', rejected: 'badge-rose', cancelled: 'badge-rose', no_show: 'badge-rose', skipped: 'badge-rose',
                on_hold: 'badge-violet', claimed: 'badge-violet'
            };
            return map[status] || 'badge-neutral';
        };

        var priorityColor = function (priority) {
            if (priority === 'urgent' || priority === 'high') return '#fb7185';
            if (priority === 'medium') return '#fbbf24';
            return '#60a5fa';
        };

        var map = L.map(host).setView(CENTER, ZOOM);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        /* barangay centre marker */
        L.circleMarker(CENTER, {
            radius: 8,
            color: '#8b5cf6',
            weight: 3,
            fillColor: '#8b5cf6',
            fillOpacity: 0.4
        }).addTo(map).bindPopup('<strong>Barangay Hall</strong><br><span class="muted">Barangay centre</span>');

        var complaintLayer = L.layerGroup().addTo(map);
        var markersById = {};

        var renderPins = function (rows) {
            complaintLayer.clearLayers();
            markersById = {};

            var list = document.getElementById('mapList');
            var count = document.getElementById('pinCount');
            if (count) count.textContent = (rows || []).length + ' pinned';

            if (!list) return;

            if (!rows || !rows.length) {
                list.innerHTML = '<div class="empty">' +
                    '<div class="ico"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>' +
                    '<h3>No pinned complaints</h3>' +
                    '<p>Reports with coordinates appear here as soon as residents file them.</p>' +
                    '</div>';
                return;
            }

            list.innerHTML = rows.map(function (row) {
                return '<div class="queue-row" data-pin="' + esc(row.id) + '">' +
                    '<span class="grow col">' +
                        '<span class="bold small">' + esc(row.title) + '</span>' +
                        '<span class="tiny dim">' + esc(row.reference_no) + ' · ' + esc(row.location) + '</span>' +
                    '</span>' +
                    '<span class="badge ' + statusClass(row.status) + '">' + esc(String(row.status).replace(/_/g, ' ')) + '</span>' +
                    '</div>';
            }).join('');

            list.querySelectorAll('[data-pin]').forEach(function (node) {
                var marker = markersById[node.getAttribute('data-pin')];
                if (marker) {
                    node.addEventListener('click', function () {
                        marker.openPopup();
                        map.panTo(marker.getLatLng());
                    });
                }
            });
        };

        window.pollUrl('{{ url('/api/map/complaints') }}', function (rows) {
            if (!Array.isArray(rows)) return;

            complaintLayer.clearLayers();
            markersById = {};

            rows.forEach(function (row) {
                if (row.latitude == null || row.longitude == null) return;

                var color = priorityColor(row.priority);
                var marker = L.circleMarker([row.latitude, row.longitude], {
                    radius: 8,
                    color: color,
                    weight: 3,
                    fillColor: color,
                    fillOpacity: 0.35
                });

                marker.bindPopup(
                    '<strong>' + esc(row.title) + '</strong><br>' +
                    '<span class="mono dim">' + esc(row.reference_no) + '</span><br>' +
                    esc(row.location) + '<br>' +
                    '<span class="badge ' + statusClass(row.status) + '">' + esc(String(row.status).replace(/_/g, ' ')) + '</span>'
                );

                marker.addTo(complaintLayer);
                markersById[row.id] = marker;
            });

            renderPins(rows);
        }, 30000);

        fetch('{{ url('/api/map/points') }}', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (points) {
                if (!Array.isArray(points)) return;

                points.forEach(function (point) {
                    if (point.latitude == null || point.longitude == null) return;

                    L.circleMarker([point.latitude, point.longitude], {
                        radius: 6,
                        color: '#34d399',
                        weight: 2,
                        fillColor: '#34d399',
                        fillOpacity: 0.3
                    }).addTo(map).bindPopup('<strong>' + esc(point.label) + '</strong><br><span class="muted">' + esc(point.type || 'point') + '</span>');
                });
            })
            .catch(function () {});

        setTimeout(function () { map.invalidateSize(); }, 300);
    });
</script>
@endpush
