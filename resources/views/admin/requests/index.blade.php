@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Document requests — '.config('app.name'))
@section('topbar-title', 'Document requests')

@section('content')
    @php
        $statusBadge = [
            'pending' => 'badge-cyan',
            'under_review' => 'badge-amber',
            'approved' => 'badge-green',
            'ready_for_release' => 'badge-amber',
            'released' => 'badge-green',
            'rejected' => 'badge-rose',
        ];
        $currentStatus = request('status');
        $currentPage = $requests->currentPage();
        $lastPage = $requests->lastPage();
        $windowStart = max(1, $currentPage - 2);
        $windowEnd = min($lastPage, $currentPage + 2);
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Records desk</span>
            <h1>Document requests</h1>
            <p class="sub">Certificates, clearances and IDs queued for processing and release.</p>
        </div>
        <div class="row">
            <span class="badge badge-cyan">{{ ($counts['pending'] ?? 0) + ($counts['under_review'] ?? 0) }} in the queue</span>
        </div>
    </div>

    @include('partials.status-legend')

    {{-- ============ status tabs ============ --}}
    @php
        /* The tabs carry the counts, so they are the status control; the filter
           row's duplicate dropdown is gone. $keep carries the search term and
           the barangay drill-down across a tab change instead of resetting
           everything else to "All". */
        $keep = fn (?string $status) => array_filter(
            array_merge(request()->query(), ['status' => $status, 'page' => null])
        );
    @endphp
    <div class="tabs">
        <a href="{{ route('admin.requests.index', $keep(null)) }}" class="tab {{ ! $currentStatus ? 'active' : '' }}">
            All <span class="count">{{ $requests->total() }}</span>
        </a>
        @foreach ($statuses as $key => $label)
            <a href="{{ route('admin.requests.index', $keep($key)) }}" class="tab {{ $currentStatus === $key ? 'active' : '' }}">
                {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ filters ============ --}}
    <form method="GET" data-filter-ajax action="{{ route('admin.requests.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search reference number or resident…"
                    value="{{ old('q', request('q')) }}">
            </div>

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>

            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost">Reset</a>
            @endif
            <span class="small dim grow right" data-result-count>{{ $requests->total() }} request(s)</span>
        </div>
    </form>

    {{-- ============ results ============ --}}
    <section class="card">
        @if ($requests->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="file" size="22" /></div>
                <h3>No requests found</h3>
                <p>Nothing matches this filter. Residents&rsquo; submissions will appear here the moment they file one.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Document</th>
                            <th>Requester</th>
                            <th>Copies / fee</th>
                            <th>Status</th>
                            <th>Filed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $doc)
                            <tr>
                                <td class="mono tiny nowrap">{{ $doc->reference_no }}</td>
                                <td class="strong">
                                    <a href="{{ route('admin.requests.show', $doc) }}">{{ $doc->typeName() }}</a>
                                    <div class="tiny dim">{{ \Illuminate\Support\Str::limit($doc->purpose, 70) }}</div>
                                </td>
                                <td class="nowrap">
                                    {{ $doc->resident?->name ?? 'Resident' }}
                                    <div class="tiny dim">{{ $doc->resident?->purok ?: '—' }}</div>
                                </td>
                                <td class="nowrap">
                                    {{ $doc->copies }} cop{{ (int) $doc->copies === 1 ? 'y' : 'ies' }}
                                    <div class="tiny dim">
                                        ₱ {{ number_format((float) $doc->fee, 2) }}
                                        &middot; {{ $doc->feeBreakdown() }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge[$doc->status] ?? 'badge-neutral' }}">{{ $doc->statusLabel() }}</span>
                                    @if ($doc->released_at)
                                        <div class="tiny dim">released {{ $doc->released_at->format('M j') }}</div>
                                    @endif
                                </td>
                                <td class="nowrap tiny dim">{{ $doc->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="card-foot">
                    <nav class="pagination mt-0">
                        @if ($requests->onFirstPage())
                            <span class="dim">&lsaquo;</span>
                        @else
                            <a href="{{ $requests->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @if ($windowStart > 1)
                            <a href="{{ $requests->url(1) }}">1</a>
                            @if ($windowStart > 2)<span class="dim">…</span>@endif
                        @endif

                        @for ($i = $windowStart; $i <= $windowEnd; $i++)
                            @if ($i === $currentPage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ $requests->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($windowEnd < $lastPage)
                            @if ($windowEnd < $lastPage - 1)<span class="dim">…</span>@endif
                            <a href="{{ $requests->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        @if ($requests->hasMorePages())
                            <a href="{{ $requests->nextPageUrl() }}">&rsaquo;</a>
                        @else
                            <span class="dim">&rsaquo;</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>
@endsection
