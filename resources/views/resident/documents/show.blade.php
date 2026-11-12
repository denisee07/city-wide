@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', $document->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Document Request')

@php($badge = fn (string $s) => match ($s) {
    'received', 'pending', 'waiting' => 'badge-cyan',
    'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber',
    'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green',
    'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose',
    'on_hold', 'claimed' => 'badge-violet',
    default => 'badge-neutral',
})
@php($stage = (int) match ($document->status) {
    'under_review' => 2,
    'approved' => 3,
    'ready_for_release' => 4,
    'released' => 5,
    default => 1,
})
@php($rejected = $document->status === 'rejected')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Document request</span>
            <h1>{{ $document->typeName() }}</h1>
            <p class="sub">
                <span class="mono">{{ $document->reference_no }}</span>
                &middot; Filed {{ $document->created_at->format('M d, Y \a\t h:i A') }}
            </p>
        </div>
        <div class="row">
            @if ($document->isPrintable())
                {{-- Once approved the request has an actual document behind it, so
                     that - not the tracking page - is what this prints. --}}
                <a href="{{ route('resident.documents.certificate', $document) }}" class="btn btn-primary">
                    <x-icon name="printer" size="16" /> Print certificate
                </a>
            @else
                <button class="btn btn-ghost" type="button" onclick="window.print()">
                    <x-icon name="printer" size="16" /> Print
                </button>
            @endif
            <a href="{{ route('resident.documents.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> All requests
            </a>
            {{-- Three actions in this head, only one of which is the point of
                 the page. Two btn-primary side by side ("Print certificate" and
                 "New request") gave the eye two equally loud answers, so the
                 start-a-new-one link is now visibly secondary. --}}
            <a href="{{ route('resident.documents.create') }}" class="btn">
                <x-icon name="plus" size="14" /> New request
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <div class="stack">
            <div class="card">
                <div class="card-body">
                    <div class="row between">
                        <div class="row">
                            <span class="badge {{ $badge($document->status) }}">{{ $document->statusLabel() }}</span>
                            <span class="badge badge-violet">{{ $document->copies }} cop{{ (int) $document->copies === 1 ? 'y' : 'ies' }}</span>
                        </div>
                        <button class="btn btn-sm" data-copy="{{ $document->reference_no }}">
                            <x-icon name="clipboard" size="14" /> Copy reference
                        </button>
                    </div>

                    <div class="steps mt-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="st {{ $rejected ? 'rejected' : ($i <= $stage ? 'on' : '') }}"></span>
                        @endfor
                    </div>

                    <div class="row between mt-1">
                        <span class="tiny {{ !$rejected && $stage >= 1 ? 'muted' : 'dim' }}">Pending</span>
                        <span class="tiny {{ !$rejected && $stage >= 2 ? 'muted' : 'dim' }}">Review</span>
                        <span class="tiny {{ !$rejected && $stage >= 3 ? 'muted' : 'dim' }}">Approved</span>
                        <span class="tiny {{ !$rejected && $stage >= 4 ? 'muted' : 'dim' }}">Ready</span>
                        <span class="tiny {{ !$rejected && $stage >= 5 ? 'muted' : 'dim' }}">Released</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="file" /> Request summary</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Document</dt><dd>{{ $document->typeName() }}</dd></div>
                        <div class="d"><dt>Purpose</dt><dd>{{ $document->purpose }}</dd></div>
                        <div class="d"><dt>Copies</dt><dd>{{ $document->copies }} — {{ $document->feeBreakdown() }}</dd></div>
                        <div class="d"><dt>Fee due</dt><dd class="mono">₱{{ number_format((float) $document->fee, 2) }}</dd></div>
                        <div class="d"><dt>Filed</dt><dd>{{ $document->created_at->format('M d, Y · h:i A') }}</dd></div>
                        <div class="d"><dt>Processed by</dt><dd>{{ $document->processor?->name ?? 'Awaiting assignment' }}</dd></div>
                        @if ($document->released_at)
                            <div class="d"><dt>Released</dt><dd>{{ $document->released_at->format('M d, Y · h:i A') }}</dd></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> Remarks from the records desk</h2>
                </div>
                <div class="card-body">
                    @if ($document->remarks)
                        <p class="muted mb-0">{{ $document->remarks }}</p>
                    @else
                        <p class="small dim mb-0">No remarks yet — the records desk will note anything you need to know here.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="check-circle" /> Current status</h2>
                    <span class="badge {{ $badge($document->status) }}">{{ $document->statusLabel() }}</span>
                </div>
                <div class="card-body">
                    @if ($document->status === 'pending')
                        <div class="alert alert-info">
                            <x-icon name="clock" size="16" />
                            <div><strong>Queued for review.</strong><p class="small mb-0">Your request is in the records desk queue.</p></div>
                        </div>
                    @elseif ($document->status === 'under_review')
                        <div class="alert alert-warning">
                            <x-icon name="eye" size="16" />
                            <div><strong>Being verified.</strong><p class="small mb-0">Your record is being checked against barangay files.</p></div>
                        </div>
                    @elseif ($document->status === 'approved')
                        <div class="alert alert-warning">
                            <x-icon name="check" size="16" />
                            <div><strong>Approved.</strong><p class="small mb-0">The document is being prepared and signed for release.</p></div>
                        </div>
                    @elseif ($document->status === 'ready_for_release')
                        <div class="alert alert-warning">
                            <x-icon name="gift" size="16" />
                            <div>
                                <strong>Ready for claiming!</strong>
                                <p class="small mb-0">Visit the barangay records desk, present <span class="mono">{{ $document->reference_no }}</span> and a valid ID, and pay ₱{{ number_format((float) $document->fee, 2) }}.</p>
                            </div>
                        </div>
                    @elseif ($document->status === 'released')
                        <div class="alert alert-success">
                            <x-icon name="check-circle" size="16" />
                            <div><strong>Released.</strong><p class="small mb-0">Claimed on {{ $document->released_at?->format('M d, Y \a\t h:i A') ?? 'file' }}. Thank you!</p></div>
                        </div>
                    @else
                        <div class="alert alert-error">
                            <x-icon name="x" size="16" />
                            <div><strong>Not approved.</strong><p class="small mb-0">See the remarks below and file a new request once requirements are complete.</p></div>
                        </div>
                    @endif

                    <p class="tiny dim mt-2 mb-0">Average turnaround: 1–2 working days, Monday to Friday, 8:00 AM – 5:00 PM.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="map" /> Claiming location</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Office</dt><dd>Barangay Records Desk</dd></div>
                        <div class="d"><dt>Hours</dt><dd>Mon–Fri, 8:00 AM – 5:00 PM</dd></div>
                        <div class="d"><dt>Bring</dt><dd>Valid ID + DOC number</dd></div>
                    </div>
                    <a href="{{ route('resident.queue.index') }}" class="btn btn-block mt-2">
                        <x-icon name="ticket" /> Take a queue number first
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
