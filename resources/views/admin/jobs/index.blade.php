@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Livelihood & Jobs — '.config('app.name'))
@section('topbar-title', 'Livelihood & Jobs')

@section('content')
    @php($statusLabels = ['open' => 'Open', 'closed' => 'Closed'])

    <div class="page-head">
        <div>
            <span class="eyebrow">Publishing</span>
            <h1>Livelihood &amp; jobs</h1>
            <p class="sub">Job posts shared with residents through the portal and public website.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="16" /> New job post
            </a>
        </div>
    </div>

    <form method="GET" data-filter-ajax action="{{ route('admin.jobs.index') }}">
        <div class="toolbar">
            <div class="row" style="gap:.45rem">
                {{-- Chips are the status control, and ListFilters carries the
                   search term and category across a chip change instead of
                   silently dropping them back to blank. --}}
                <a href="{{ route('admin.jobs.index', \App\Support\ListFilters::with('status', null)) }}" class="chip {{ $active === 'all' ? 'active' : '' }}">
                    All <span class="tiny dim">{{ $total }}</span>
                </a>
                <a href="{{ route('admin.jobs.index', \App\Support\ListFilters::with('status', 'open')) }}" class="chip {{ $active === 'open' ? 'active' : '' }}">
                    Open <span class="tiny dim">{{ $openCount }}</span>
                </a>
                <a href="{{ route('admin.jobs.index', \App\Support\ListFilters::with('status', 'closed')) }}" class="chip {{ $active === 'closed' ? 'active' : '' }}">
                    Closed <span class="tiny dim">{{ $closedCount }}</span>
                </a>
            </div>

            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search title, employer or place…"
                    value="{{ old('q', request('q')) }}">
            </div>

            <select name="category" class="select filter-select">
                <option value="">All categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Carries the chip choice into the form so pressing Apply does not
                 quietly return you to "All". --}}
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>

            @if (request()->hasAny(['q', 'category', 'status']))
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    @if ($jobs->isEmpty())
        <section class="card">
            <div class="empty">
                <div class="ico"><x-icon name="briefcase" size="22" /></div>
                <h3>No job posts found</h3>
                <p>Nothing matches this filter. Post an opening so residents can apply through the portal.</p>
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" size="15" /> Create a job post
                </a>
            </div>
        </section>
    @else
        <div class="grid grid-2">
            @foreach ($jobs as $job)
                <article class="job-card">
                    <div class="row between items-center">
                        <div class="row gap-1">
                            <span class="badge {{ $badge[$job->status] ?? 'badge-neutral' }}">{{ $statusLabels[$job->status] ?? ucfirst($job->status) }}</span>
                            @if ($job->is_featured)
                                <span class="pin-note"><x-icon name="star" size="12" /> Featured</span>
                            @endif
                        </div>
                        <span class="badge badge-blue">{{ $job->categoryName() }}</span>
                    </div>

                    <div>
                        <h3>{{ $job->title }}</h3>
                        <div class="co">{{ $job->company }}</div>
                    </div>

                    <div class="meta">
                        <span class="row" style="gap:.3rem"><x-icon name="pin" size="13" /> {{ $job->location }}</span>
                        <span class="row" style="gap:.3rem"><x-icon name="clock" size="13" /> {{ $job->typeName() }}</span>
                        @if ($job->salary)
                            <span class="row" style="gap:.3rem"><x-icon name="star" size="13" /> {{ $job->salary }}</span>
                        @endif
                        @if ($job->deadline)
                            <span class="row" style="gap:.3rem"><x-icon name="calendar" size="13" /> until {{ $job->deadline->format('M j, Y') }}</span>
                        @endif
                    </div>

                    <p class="small muted mb-0">{{ \Illuminate\Support\Str::limit($job->description, 170) }}</p>

                    <div class="row between mt-1">
                        <span class="tiny dim">Posted {{ $job->created_at->diffForHumans() }} by {{ $job->poster?->name ?? 'Barangay office' }}</span>
                    </div>

                    <div class="row between">
                        <span class="tiny dim">{{ $job->contact_person ?: 'Contact the barangay hall' }}</span>
                        <div class="row" style="gap:.4rem">
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-sm btn-info">
                                <x-icon name="edit" size="14" /> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}"
                                data-confirm="Delete &quot;{{ $job->title }}&quot; from the livelihood board?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Delete">
                                    <x-icon name="trash" size="14" />
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
