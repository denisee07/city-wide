@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Complaint '.$complaint->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Complaint detail')

@section('content')
    @php
        $statusBadge = \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES;
        $statusLabels = \App\Http\Controllers\Admin\ComplaintController::STATUSES;
        $priorityBadge = \App\Http\Controllers\Admin\ComplaintController::PRIORITY_BADGES;
        $priorityLabels = \App\Http\Controllers\Admin\ComplaintController::PRIORITIES;
        $categories = \App\Http\Controllers\Admin\ComplaintController::CATEGORIES;

        // One definition of the tracker, shared with ComplaintController so an
        // in-place ajax refresh of the card matches this first render.
        $events = \App\Support\ComplaintTracker::events($complaint);

        $hasCoords = is_numeric($complaint->latitude) && is_numeric($complaint->longitude);
        $resident = $complaint->resident;
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Case {{ $complaint->reference_no }}</span>
            <h1>{{ $complaint->title }}</h1>
            <p class="sub">
                Filed {{ $complaint->created_at->format('M j, Y \a\t g:i A') }}
                by {{ $resident?->name ?? 'a resident' }}
                @if ($complaint->resolved_at) · resolved {{ $complaint->resolved_at->format('M j, Y') }} @endif
            </p>
        </div>
        <div class="row">
            {{-- Rewritten in place after an ajax status save; see ComplaintController. --}}
        <span class="row" data-case-badges>
            @include('admin.complaints.partials.case-badges')
        </span>
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="15" /> Back to list
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        {{-- ==================== main column ==================== --}}
        <div class="stack">

            {{-- summary --}}
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="file" /> Case summary</h2>
                    @if ($complaint->admin_remarks)
                        <span class="badge badge-violet">Remarks on file</span>
                    @endif
                </div>
                <div class="card-body">
                    <p style="white-space:pre-wrap">{{ $complaint->description }}</p>

                    <hr class="divider">

                    <div class="detail-list" id="caseFacts">
                        @include('admin.complaints.partials.case-facts')
                    </div>

                    @if ($complaint->admin_remarks)
                        <div class="alert alert-info mt-2 mb-0">
                            <x-icon name="info" size="17" />
                            <div><strong>Latest remarks</strong><p>{{ $complaint->admin_remarks }}</p></div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- status update --}}
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="sliders" /> Update case status</h2>
                    <span class="tiny dim">Applies immediately and is logged in the tracker</span>
                </div>
                <form method="POST" data-ajax action="{{ route('admin.complaints.update', $complaint) }}">
                    @csrf
                    @method('PATCH')

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label class="label" for="status">Status <span class="req">*</span></label>
                                <select id="status" name="status" class="select">
                                    @foreach ($statusLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $complaint->status) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field">
                                <label class="label" for="priority">Priority <span class="req">*</span></label>
                                <select id="priority" name="priority" class="select">
                                    @foreach ($priorityLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(old('priority', $complaint->priority) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('priority')<div class="error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field">
                                <label class="label" for="assigned_to">Assigned handler</label>
                                <select id="assigned_to" name="assigned_to" class="select">
                                    <option value="">— Unassigned —</option>
                                    @foreach ($assignees as $member)
                                        <option value="{{ $member->id }}" @selected((string) old('assigned_to', $complaint->assigned_to) === (string) $member->id)>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')<div class="error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field span-2">
                                <label class="label" for="admin_remarks">Admin remarks</label>
                                <textarea id="admin_remarks" name="admin_remarks" class="textarea" rows="3"
                                    placeholder="Actions taken, instructions to the complainant, resolution notes…">{{ old('admin_remarks', $complaint->admin_remarks) }}</textarea>
                                <div class="help">Visible to the resident on their complaint tracker.</div>
                                @error('admin_remarks')<div class="error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-foot row between">
                        <span class="tiny dim">Last touched {{ $complaint->updated_at->diffForHumans() }}</span>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Save changes
                        </button>
                    </div>
                </form>
            </section>

            {{-- tracker --}}
            <section class="card" id="caseTracker">
                @include('admin.complaints.partials.tracker')
            </section>
        </div>

        {{-- ==================== sidebar ==================== --}}
        <div class="stack">
            {{-- resident --}}
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="user" /> Complainant</h2>
                </div>
                <div class="card-body">
                    <div class="row gap-2 items-center mb-2">
                        <span class="avatar lg {{ $resident ? 'violet' : 'neutral' }}">{{ $resident?->initials ?? '—' }}</span>
                        <div class="grow" style="min-width:0">
                            <div class="bold">{{ $resident?->name ?? 'Deleted account' }}</div>
                            <div class="tiny dim">{{ $resident?->email ?? 'No e-mail on file' }}</div>
                        </div>
                    </div>

                    <div class="detail-list">
                        <div class="d"><dt>Mobile</dt><dd>{{ $resident?->phone ?: '—' }}</dd></div>
                        <div class="d"><dt>Purok</dt><dd>{{ $resident?->purok ?: '—' }}</dd></div>
                        <div class="d"><dt>Address</dt><dd>{{ $resident?->address ?: '—' }}</dd></div>
                        <div class="d"><dt>Registered</dt><dd>{{ $resident?->created_at?->format('M j, Y') ?? '—' }}</dd></div>
                    </div>

                    <div class="grid grid-3 gap-1 mt-2 center">
                        <div>
                            <div class="bold" style="font-size:1.25rem">{{ $residentStats['complaints'] }}</div>
                            <div class="tiny dim">Reports</div>
                        </div>
                        <div>
                            <div class="bold" style="font-size:1.25rem">{{ $residentStats['requests'] }}</div>
                            <div class="tiny dim">Documents</div>
                        </div>
                        <div>
                            <div class="bold" style="font-size:1.25rem">{{ $residentStats['appointments'] }}</div>
                            <div class="tiny dim">Appointments</div>
                        </div>
                    </div>

                    @if ($resident)
                        <a href="{{ route('admin.users.index', ['q' => $resident->name]) }}" class="btn btn-ghost btn-block btn-sm mt-2">
                            <x-icon name="users" size="15" /> Open resident record
                        </a>
                    @endif
                </div>
            </section>

            {{-- case facts --}}
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clipboard" /> Case facts</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d">
                            <dt>Reference</dt>
                            <dd class="row" style="gap:.45rem">
                                <span class="kbd mono">{{ $complaint->reference_no }}</span>
                                <button type="button" class="btn btn-sm btn-ghost" data-copy="{{ $complaint->reference_no }}">Copy</button>
                            </dd>
                        </div>
                        <div class="d"><dt>Status</dt><dd><span class="badge {{ $statusBadge[$complaint->status] ?? 'badge-neutral' }}">{{ $statusLabels[$complaint->status] ?? $complaint->status }}</span></dd></div>
                        <div class="d"><dt>Priority</dt><dd><span class="badge {{ $priorityBadge[$complaint->priority] ?? 'badge-neutral' }}">{{ $priorityLabels[$complaint->priority] ?? $complaint->priority }}</span></dd></div>
                        <div class="d">
                            <dt>Handler</dt>
                            <dd>{{ $complaint->assignee?->name ?? 'Unassigned' }}</dd>
                        </div>
                        <div class="d"><dt>Resolved</dt><dd>{{ $complaint->resolved_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                    </div>
                </div>
            </section>

            {{-- map --}}
            @if ($hasCoords)
                @push('styles')<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">@endpush
                @push('scripts')<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>@endpush

                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="pin" /> Location</h2>
                        <span class="badge badge-cyan">Pinned</span>
                    </div>
                    <div class="card-body">
                        <div id="caseMap" class="map-wrap short"
                            data-lat="{{ $complaint->latitude }}" data-lng="{{ $complaint->longitude }}"
                            data-title="{{ $complaint->title }}" data-location="{{ $complaint->location }}"></div>
                        <div class="map-legend">
                            <span><i style="background:#22d3ee"></i>{{ $complaint->location }}</span>
                        </div>
                    </div>
                </section>
            @else
                <section class="card">
                    <div class="card-body">
                        <div class="empty" style="padding:1.4rem 0">
                            <div class="ico"><x-icon name="pin" size="22" /></div>
                            <h3>No coordinates</h3>
                            <p class="mb-0">This report was filed without a map pin.</p>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if ($hasCoords)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var el = document.getElementById('caseMap');
                    if (!el || typeof L === 'undefined') return;

                    var lat = parseFloat(el.getAttribute('data-lat'));
                    var lng = parseFloat(el.getAttribute('data-lng'));

                    function esc(value) {
                        return String(value === null || value === undefined ? '' : value)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;');
                    }

                    var popup = '<strong>' + esc(el.getAttribute('data-title')) + '</strong><br>' +
                        esc(el.getAttribute('data-location'));

                    var map = L.map(el).setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    L.circleMarker([lat, lng], {
                        radius: 11,
                        color: '#22d3ee',
                        weight: 3,
                        fillColor: '#22d3ee',
                        fillOpacity: .35
                    }).addTo(map).bindPopup(popup);
                });
            </script>
        @endpush
    @endif
@endsection
