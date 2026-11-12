@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'My Complaints — '.config('app.name'))
@section('topbar-title', 'My Complaints')

@php($badge = fn (string $s) => match ($s) { 'received', 'pending', 'waiting' => 'badge-cyan', 'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber', 'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green', 'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose', 'on_hold', 'claimed' => 'badge-violet', default => 'badge-neutral' })
@php($priorityBadge = fn (string $p) => match ($p) { 'urgent' => 'badge-rose', 'high' => 'badge-amber', 'medium' => 'badge-blue', default => 'badge-neutral' })
@php($tabUrl = fn (string $s) => route('resident.complaints.index', array_filter(['status' => $s !== '' ? $s : null, 'category' => $category !== '' ? $category : null, 'q' => $q !== '' ? $q : null])))

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Peace of mind, tracked</span>
            <h1>My complaints</h1>
            <p class="sub">Every report you have filed with the barangay — from intake to resolution.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> File a complaint
            </a>
        </div>
    </div>

    @include('partials.status-legend', ['showPriority' => true])

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

    <form class="toolbar" method="GET" data-filter-ajax action="{{ route('resident.complaints.index') }}">
        @if ($status !== '')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="search-box grow">
            <input class="input" type="search" name="q" value="{{ old('q', $q) }}" placeholder="Search reference no., title or location…">
        </div>

        <div>
            <select class="select" name="category" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit"><x-icon name="search" /> Search</button>

        @if ($status !== '' || $category !== '' || $q !== '')
            <a href="{{ route('resident.complaints.index') }}" class="btn btn-ghost">Clear filters</a>
        @endif
    </form>

    <div class="card">
        @if ($complaints->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="alert" size="26" /></div>
                <h3>{{ $q || $status || $category ? 'No complaints match these filters' : 'You have not filed anything yet' }}</h3>
                <p>{{ $q || $status || $category ? 'Try a different status, category or search term.' : 'Report a barangay concern and follow its progress step by step.' }}</p>
                <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                    <x-icon name="plus" /> File a complaint
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Concern</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Filed</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td class="mono nowrap">{{ $complaint->reference_no }}</td>
                                <td class="strong">
                                    {{ $complaint->title }}
                                    <div class="tiny dim">{{ $categories[$complaint->category] ?? ucfirst($complaint->category) }} &middot; {{ $complaint->location }}</div>
                                </td>
                                <td><span class="badge {{ $priorityBadge($complaint->priority) }}">{{ ucfirst($complaint->priority) }}</span></td>
                                <td><span class="badge {{ $badge($complaint->status) }}">{{ $statuses[$complaint->status] ?? ucfirst($complaint->status) }}</span></td>
                                <td class="nowrap">{{ $complaint->created_at->format('M d, Y') }}</td>
                                <td class="right">
                                    <a href="{{ route('resident.complaints.show', $complaint) }}" class="btn btn-sm">
                                        Track <x-icon name="chevron" size="14" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($complaints->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $complaints->currentPage() - 2), max(1, min($complaints->lastPage(), $complaints->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($complaints->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $complaints->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $complaints->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($complaints->hasMorePages())
                <a href="{{ $complaints->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif
@endsection

