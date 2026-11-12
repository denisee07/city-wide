@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Documents & Certificates — '.config('app.name'))
@section('topbar-title', 'Documents & Certificates')

@php($badge = fn (string $s) => match ($s) {
    'received', 'pending', 'waiting' => 'badge-cyan',
    'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber',
    'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green',
    'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose',
    'on_hold', 'claimed' => 'badge-violet',
    default => 'badge-neutral',
})
@php($tabUrl = fn (string $s) => route('resident.documents.index', array_filter(['status' => $s !== '' ? $s : null])))

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Records desk</span>
            <h1>Documents &amp; Certificates</h1>
            <p class="sub">Request clearances, certificates and endorsements — then track them until release.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.documents.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> Request a document
            </a>
        </div>
    </div>

    @include('partials.status-legend')

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

    <div class="card">
        @if ($requests->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="file" size="26" /></div>
                <h3>{{ $status !== '' ? 'No requests with this status' : 'No document requests yet' }}</h3>
                <p>{{ $status !== '' ? 'Switch tabs to see your other requests.' : 'Barangay clearance, certificate of residency and more — request one in under a minute.' }}</p>
                <a href="{{ route('resident.documents.create') }}" class="btn btn-primary">
                    <x-icon name="plus" /> Request a document
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Document</th>
                            <th>Purpose</th>
                            <th>Copies</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $item)
                            <tr>
                                <td class="mono nowrap">{{ $item->reference_no }}</td>
                                <td class="strong">
                                    {{ $item->typeName() }}
                                    <div class="tiny dim">Filed {{ $item->created_at->format('M d, Y') }}</div>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($item->purpose, 46) }}</td>
                                <td class="mono">{{ $item->copies }}</td>
                                <td class="mono nowrap">
                                    ₱{{ number_format((float) $item->fee, 2) }}
                                    <div class="tiny dim">{{ $item->feeBreakdown() }}</div>
                                </td>
                                <td><span class="badge {{ $badge($item->status) }}">{{ $item->statusLabel() }}</span></td>
                                <td class="right">
                                    <a href="{{ route('resident.documents.show', $item) }}" class="btn btn-sm">
                                        Details <x-icon name="chevron" size="14" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($requests->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $requests->currentPage() - 2), max(1, min($requests->lastPage(), $requests->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($requests->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $requests->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $requests->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($requests->hasMorePages())
                <a href="{{ $requests->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif
@endsection

