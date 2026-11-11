@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Complaints — '.config('app.name'))
@section('topbar-title', 'Complaints')

@section('content')
    @php
        $statusBadge = \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES;
        $statusLabels = \App\Http\Controllers\Admin\ComplaintController::STATUSES;
        $priorityBadge = \App\Http\Controllers\Admin\ComplaintController::PRIORITY_BADGES;
        $priorityLabels = \App\Http\Controllers\Admin\ComplaintController::PRIORITIES;
        $categories = \App\Http\Controllers\Admin\ComplaintController::CATEGORIES;
        $currentStatus = request('status');
        $currentPage = $complaints->currentPage();
        $lastPage = $complaints->lastPage();
        $windowStart = max(1, $currentPage - 2);
        $windowEnd = min($lastPage, $currentPage + 2);
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Case management</span>
            <h1>Complaints</h1>
            <p class="sub">Track, assign and resolve reports filed by residents.</p>
        </div>
        {{-- The map is a sidebar row already; a second route to it here only
             made the header noisy. --}}
    </div>

    @include('partials.status-legend', ['showPriority' => true])

    {{-- ============ status tabs ============ --}}
    {{-- The tabs are the status control; the filter row no longer carries a
         second one that could disagree with what is already picked.
         ListFilters carries the other choices (and the barangay drill-down)
         across a tab change, which until now reset everything to "All". --}}
    <div class="tabs">
        <a href="{{ route('admin.complaints.index', \App\Support\ListFilters::with('status', null)) }}" class="tab {{ ! $currentStatus ? 'active' : '' }}">
            All <span class="count">{{ $complaints->total() }}</span>
        </a>
        @foreach ($statusLabels as $key => $label)
            <a href="{{ route('admin.complaints.index', \App\Support\ListFilters::with('status', $key)) }}" class="tab {{ $currentStatus === $key ? 'active' : '' }}">
                {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ filters ============ --}}
    <form method="GET" data-filter-ajax action="{{ route('admin.complaints.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search reference, title, place or resident…" value="{{ old('q', request('q')) }}">
            </div>

            <select name="priority" class="select filter-select">
                <option value="">All priorities</option>
                @foreach ($priorityLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('priority') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="category" class="select filter-select">
                <option value="">All categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                <x-icon name="search" size="15" /> Apply
            </button>

            {{-- Only shown when there is something to undo: with every filter
                 already on "All", a permanent Reset looked like a broken
                 refresh button. --}}
            @if (request()->hasAny(['q', 'priority', 'category', 'status']))
                <a href="{{ route('admin.complaints.index') }}" class="btn btn-ghost">Reset</a>
            @endif

            <span class="small dim grow right" data-result-count>{{ $complaints->total() }} matching report(s)</span>
        </div>
    </form>

    {{-- ============ results ============ --}}
    <section class="card">
        @if ($complaints->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="search" size="22" /></div>
                <h3>No complaints match these filters</h3>
                <p>Try a different status, priority or search term.</p>
                {{-- The Reset above is the one clear-filters control; a second
                     copy down here pointed at the same place. --}}
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Complaint</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Filed</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td class="mono tiny nowrap">{{ $complaint->reference_no }}</td>
                                <td class="strong">
                                    <a href="{{ route('admin.complaints.show', $complaint) }}">{{ $complaint->title }}</a>
                                    <div class="tiny dim">{{ $complaint->resident?->name ?? 'Resident' }} · {{ $complaint->location }}</div>
                                </td>
                                <td class="nowrap">{{ $categories[$complaint->category] ?? $complaint->category }}</td>
                                <td><span class="badge {{ $priorityBadge[$complaint->priority] ?? 'badge-neutral' }}">{{ $priorityLabels[$complaint->priority] ?? $complaint->priority }}</span></td>
                                <td><span class="badge {{ $statusBadge[$complaint->status] ?? 'badge-neutral' }}">{{ $statusLabels[$complaint->status] ?? $complaint->status }}</span></td>
                                <td class="nowrap tiny dim">{{ $complaint->created_at->format('M j, Y') }}</td>
                                <td class="nowrap">
                                    @if ($complaint->assignee)
                                        <span class="user-pill" style="padding:.15rem .3rem">
                                            <span class="avatar sm neutral">{{ $complaint->assignee->initials }}</span>
                                            <span class="nm small">{{ $complaint->assignee->name }}</span>
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">Unassigned</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($complaints->hasPages())
                <div class="card-foot">
                    <nav class="pagination mt-0">
                        @if ($complaints->onFirstPage())
                            <span class="dim">&lsaquo;</span>
                        @else
                            <a href="{{ $complaints->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @if ($windowStart > 1)
                            <a href="{{ $complaints->url(1) }}">1</a>
                            @if ($windowStart > 2)<span class="dim">…</span>@endif
                        @endif

                        @for ($i = $windowStart; $i <= $windowEnd; $i++)
                            @if ($i === $currentPage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ $complaints->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($windowEnd < $lastPage)
                            @if ($windowEnd < $lastPage - 1)<span class="dim">…</span>@endif
                            <a href="{{ $complaints->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        @if ($complaints->hasMorePages())
                            <a href="{{ $complaints->nextPageUrl() }}">&rsaquo;</a>
                        @else
                            <span class="dim">&rsaquo;</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>
@endsection
