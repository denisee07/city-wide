@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Announcements — '.config('app.name'))
@section('topbar-title', 'Announcements')

@section('content')
    @php($categories = \App\Http\Controllers\Admin\AnnouncementController::CATEGORIES)

    <div class="page-head">
        <div>
            <span class="eyebrow">Publishing</span>
            <h1>Announcements</h1>
            <p class="sub">Bulletins, advisories and event notices shown on the resident portal.</p>
        </div>
        <div class="row">
            <span class="badge badge-amber">{{ $pinnedCount }} pinned</span>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="16" /> New announcement
            </a>
        </div>
    </div>

    {{-- ============ state tabs ============ --}}
    {{-- The tabs carry the counts, so they are the state control; the second
           dropdown for the same thing (Any state / Published / Draft) is gone.
           ListFilters keeps the search term and category when you switch. --}}
    <div class="tabs">
        <a href="{{ route('admin.announcements.index', \App\Support\ListFilters::with('published', null)) }}" class="tab {{ ! request('published') ? 'active' : '' }}">
            All <span class="count">{{ $publishedCount + $draftCount }}</span>
        </a>
        <a href="{{ route('admin.announcements.index', \App\Support\ListFilters::with('published', 'published')) }}" class="tab {{ request('published') === 'published' ? 'active' : '' }}">
            Published <span class="count">{{ $publishedCount }}</span>
        </a>
        <a href="{{ route('admin.announcements.index', \App\Support\ListFilters::with('published', 'draft')) }}" class="tab {{ request('published') === 'draft' ? 'active' : '' }}">
            Drafts <span class="count">{{ $draftCount }}</span>
        </a>
    </div>

    <form method="GET" data-filter-ajax action="{{ route('admin.announcements.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search title or body…" value="{{ old('q', request('q')) }}">
            </div>

            <select name="category" class="select filter-select">
                <option value="">All categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>

            @if (request()->hasAny(['q', 'category', 'published']))
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    @if ($announcements->isEmpty())
        <section class="card">
            <div class="empty">
                <div class="ico"><x-icon name="megaphone" size="22" /></div>
                <h3>No announcements found</h3>
                <p>Nothing matches this filter yet — publish a bulletin so residents see it on their dashboard.</p>
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" size="15" /> Write an announcement
                </a>
            </div>
        </section>
    @else
        <div class="grid grid-2">
            @foreach ($announcements as $announcement)
                <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                    <div class="row between items-center mb-1">
                        <div class="row gap-1">
                            <span class="badge badge-blue">{{ $categories[$announcement->category] ?? $announcement->category }}</span>
                            <span class="badge {{ $announcement->is_published ? 'badge-green' : 'badge-cyan' }}">
                                {{ $announcement->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        @if ($announcement->is_pinned)
                            <span class="pin-note"><x-icon name="pin" size="13" /> Pinned</span>
                        @endif
                    </div>

                    <h3>{{ $announcement->title }}</h3>
                    <div class="body">{{ \Illuminate\Support\Str::limit($announcement->body, 180) }}</div>

                    <div class="row gap-1 mt-2 tiny dim">
                        @if ($announcement->event_date)
                            <span class="row" style="gap:.3rem"><x-icon name="calendar" size="13" /> {{ $announcement->event_date }}</span>
                        @endif
                        @if ($announcement->location)
                            <span class="row" style="gap:.3rem"><x-icon name="pin" size="13" /> {{ $announcement->location }}</span>
                        @endif>
                        <span>{{ ($announcement->published_at ?? $announcement->created_at)->format('M j, Y') }}</span>
                        <span>· {{ $announcement->creator?->name ?? 'Barangay office' }}</span>
                    </div>

                    <div class="row between mt-2">
                        <div class="row gap-1">
                            <a href="{{ route('admin.announcements.preview', $announcement) }}" class="btn btn-sm btn-ghost">
                                <x-icon name="eye" size="14" /> Preview
                            </a>
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-info">
                                <x-icon name="edit" size="14" /> Edit
                            </a>
                        </div>

                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
                            data-confirm="Delete this announcement? It will disappear from the resident portal immediately.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <x-icon name="trash" size="14" /> Delete
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
