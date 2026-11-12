@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Announcements — '.config('app.name'))
@section('topbar-title', 'Announcements')

@php($categoryLabel = fn (string $c) => ucwords(str_replace('_', ' ', $c)))
@php($eventLabel = fn (?string $value) => $value && strtotime($value) ? date('M d, Y', strtotime($value)) : $value)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Barangay bulletin</span>
            <h1>Announcements</h1>
            <p class="sub">Advisories, events and public notices straight from the barangay hall.</p>
        </div>
        <div class="row">
            <span class="badge badge-cyan"><x-icon name="bell" size="13" /> Published notices only</span>
        </div>
    </div>

    <div class="toolbar">
        <span class="small dim">Category</span>
        <a href="{{ route('resident.announcements.index') }}" class="chip {{ $category === '' ? 'active' : '' }}">All</a>
        @foreach ($categories as $item)
            <a href="{{ route('resident.announcements.index', ['category' => $item]) }}"
               class="chip {{ $category === $item ? 'active' : '' }}">{{ $categoryLabel($item) }}</a>
        @endforeach
    </div>

    @if ($announcements->isEmpty())
        <div class="card">
            <div class="empty">
                <div class="ico"><x-icon name="megaphone" size="26" /></div>
                <h3>{{ $category !== '' ? 'No notices in this category' : 'The bulletin board is empty' }}</h3>
                <p>{{ $category !== '' ? 'Pick another category to see published notices.' : 'Published advisories and events will show up here as soon as officials post them.' }}</p>
                <a href="{{ route('resident.announcements.index') }}" class="btn"><x-icon name="refresh" /> Show all</a>
            </div>
        </div>
    @else
        <div class="grid grid-2">
            @foreach ($announcements as $announcement)
                <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                    <div class="row between mb-1">
                        <div class="row">
                            <span class="badge badge-blue badge-plain">{{ $categoryLabel($announcement->category ?? 'general') }}</span>
                            @if ($announcement->is_pinned)
                                <span class="badge badge-amber">Pinned</span>
                            @endif
                        </div>
                        <span class="tiny dim">{{ ($announcement->published_at ?? $announcement->created_at)->format('M d, Y') }}</span>
                    </div>

                    <h3>
                        <a href="{{ route('resident.announcements.show', $announcement) }}">{{ $announcement->title }}</a>
                    </h3>

                    <div class="body">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 190) }}</div>

                    <div class="row mt-2">
                        @if ($announcement->event_date)
                            <span class="chip"><x-icon name="calendar" size="13" /> {{ $eventLabel($announcement->event_date) }}</span>
                        @endif
                        @if ($announcement->location)
                            <span class="chip"><x-icon name="pin" size="13" /> {{ $announcement->location }}</span>
                        @endif
                        <span class="grow"></span>
                        <a href="{{ route('resident.announcements.show', $announcement) }}" class="btn btn-sm">
                            Read notice <x-icon name="chevron" size="14" />
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($announcements->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $announcements->currentPage() - 2), max(1, min($announcements->lastPage(), $announcements->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($announcements->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $announcements->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $announcements->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($announcements->hasMorePages())
                <a href="{{ $announcements->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif
@endsection

