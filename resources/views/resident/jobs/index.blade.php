@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Livelihood & Jobs — '.config('app.name'))
@section('topbar-title', 'Livelihood & Jobs')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Livelihood board</span>
            <h1>Jobs &amp; opportunities</h1>
            <p class="sub">{{ $openCount }} open position{{ $openCount === 1 ? '' : 's' }} shared by employers in and around the barangay.</p>
        </div>
        <div class="row">
            <span class="badge badge-green"><x-icon name="briefcase" size="13" /> Updated {{ now()->format('M d, Y') }}</span>
        </div>
    </div>

    <form class="toolbar" method="GET" data-filter-ajax action="{{ route('resident.jobs.index') }}">
        <div class="search-box grow">
            <input class="input" type="search" name="q" value="{{ old('q', $q) }}" placeholder="Search job title, employer or location…">
        </div>

        <div>
            <select class="select" name="category" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select class="select" name="employment_type" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <option value="">All work types</option>
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" @selected(old('employment_type', $type) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit"><x-icon name="search" /> Search</button>

        @if ($q !== '' || $category !== '' || $type !== '')
            <a href="{{ route('resident.jobs.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    @if ($jobs->isEmpty())
        <div class="card">
            <div class="empty">
                <div class="ico"><x-icon name="briefcase" size="26" /></div>
                <h3>No openings match your search</h3>
                <p>Try a different keyword, category or work type — new postings arrive every week.</p>
                <a href="{{ route('resident.jobs.index') }}" class="btn"><x-icon name="refresh" /> Reset filters</a>
            </div>
        </div>
    @else
        <div class="grid grid-3">
            @foreach ($jobs as $job)
                <div class="job-card">
                    <div class="row between">
                        <span class="badge badge-blue">{{ $job->categoryName() }}</span>
                        @if ($job->is_featured)
                            <span class="badge badge-amber">Featured</span>
                        @endif
                    </div>

                    <h3><a href="{{ route('resident.jobs.show', $job) }}">{{ $job->title }}</a></h3>
                    <div class="co">{{ $job->company }}</div>

                    <div class="meta">
                        <span class="row items-center"><x-icon name="pin" size="13" /> {{ $job->location }}</span>
                        <span class="row items-center"><x-icon name="clock" size="13" /> {{ $job->typeName() }}</span>
                        <span class="row items-center"><x-icon name="star" size="13" /> {{ $job->salary ?: 'Negotiable' }}</span>
                    </div>

                    <div class="row between mt-1">
                        <span class="tiny dim">
                            @if ($job->deadline)
                                Apply until {{ $job->deadline->format('M d, Y') }}
                            @else
                                Open until filled
                            @endif
                        </span>
                        <a href="{{ route('resident.jobs.show', $job) }}" class="btn btn-sm">
                            Details <x-icon name="chevron" size="14" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($jobs->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $jobs->currentPage() - 2), max(1, min($jobs->lastPage(), $jobs->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($jobs->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $jobs->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $jobs->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($jobs->hasMorePages())
                <a href="{{ $jobs->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif
@endsection

