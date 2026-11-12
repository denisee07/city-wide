@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', $announcement->title.' — '.config('app.name'))
@section('topbar-title', 'Announcement')

@php($categoryLabel = fn (?string $c) => ucwords(str_replace('_', ' ', $c ?? 'general')))
@php($eventTs = $announcement->event_date ? strtotime($announcement->event_date) : false)
@php($eventDate = $eventTs ? \Illuminate\Support\Carbon::createFromTimestamp($eventTs) : null)
@php($eventLabel = $eventDate ? $eventDate->format('l, F j, Y') : $announcement->event_date)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Barangay bulletin</span>
            <h1>{{ $announcement->title }}</h1>
            <p class="sub">
                Published {{ ($announcement->published_at ?? $announcement->created_at)->format('F j, Y \a\t h:i A') }}
                @if ($announcement->creator)
                    &middot; by {{ $announcement->creator->name }}
                @endif
            </p>
        </div>
        <div class="row">
            <button class="btn btn-sm" data-copy="{{ $announcement->title }}">
                <x-icon name="clipboard" size="14" /> Copy title
            </button>
            <a href="{{ route('resident.announcements.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> All announcements
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <div class="stack">
            <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                <div class="row between mb-2">
                    <div class="row">
                        <span class="badge badge-blue badge-plain">{{ $categoryLabel($announcement->category) }}</span>
                        @if ($announcement->is_pinned)
                            <span class="badge badge-amber">Pinned</span>
                        @endif
                    </div>
                    <span class="tiny dim mono">{{ $announcement->id ? 'NOTICE-'.str_pad((string) $announcement->id, 4, '0', STR_PAD_LEFT) : '' }}</span>
                </div>

                <div class="body">{{ $announcement->body }}</div>
            </article>

            @if ($announcement->event_date || $announcement->location)
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="calendar" /> Event details</h2>
                    </div>
                    <div class="card-body">
                        <div class="detail-list">
                            @if ($announcement->event_date)
                                <div class="d"><dt>Date</dt><dd>{{ $eventLabel }}</dd></div>
                            @endif
                            @if ($announcement->location)
                                <div class="d"><dt>Venue</dt><dd>{{ $announcement->location }}</dd></div>
                            @endif
                            <div class="d"><dt>Category</dt><dd>{{ $categoryLabel($announcement->category) }}</dd></div>
                        </div>

                        @if ($eventDate && $eventDate->isFuture())
                            <div class="alert alert-info mt-2 mb-0">
                                <x-icon name="calendar" size="16" />
                                <div>
                                    <strong>{{ $eventDate->diffForHumans() }}</strong>
                                    <p class="small mb-0">Add it to your calendar — arrival before the start time is encouraged.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> Notice summary</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Published</dt><dd>{{ ($announcement->published_at ?? $announcement->created_at)->format('M d, Y') }}</dd></div>
                        <div class="d"><dt>Category</dt><dd>{{ $categoryLabel($announcement->category) }}</dd></div>
                        <div class="d"><dt>Status</dt><dd><span class="badge badge-green">Published</span></dd></div>
                        @if ($announcement->event_date)
                            <div class="d"><dt>Event date</dt><dd>{{ $eventLabel }}</dd></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="bell" /> Stay updated</h2>
                </div>
                <div class="card-body">
                    <p class="small muted mb-0">Announcements are posted here the moment they are approved by the barangay hall.</p>
                    {{-- The three buttons under this sentence went to the queue
                         board, the freedom wall and the map — all three already
                         sit in the Community group of the sidebar the visitor is
                         reading this from. --}}
                </div>
            </div>
        </div>
    </div>
@endsection
