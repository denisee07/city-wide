@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Lost & Found — '.config('app.name'))
@section('topbar-title', 'Lost & Found')

@php($badge = fn (string $s) => match ($s) {
    'lost' => 'badge-rose',
    'found' => 'badge-green',
    'claimed' => 'badge-violet',
    default => 'badge-neutral',
})
@php($tabUrl = fn (string $s) => route('resident.lost-found.index', array_filter([
    'status' => $s !== '' ? $s : null,
    'q' => $q !== '' ? $q : null,
])))

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Community board</span>
            <h1>Lost &amp; Found</h1>
            <p class="sub">Report what you lost or what you found — the barangay board reunites owners with their items.</p>
        </div>
        <div class="row">
            <button class="btn btn-primary" type="button" data-modal-open="reportModal">
                <x-icon name="plus" /> Report an item
            </button>
        </div>
    </div>

    <div class="tabs">
        <a href="{{ $tabUrl('') }}" class="tab {{ $status === '' ? 'active' : '' }}">
            All <span class="count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ $tabUrl('lost') }}" class="tab {{ $status === 'lost' ? 'active' : '' }}">
            Lost <span class="count">{{ $counts['lost'] }}</span>
        </a>
        <a href="{{ $tabUrl('found') }}" class="tab {{ $status === 'found' ? 'active' : '' }}">
            Found <span class="count">{{ $counts['found'] }}</span>
        </a>
    </div>

    <form class="toolbar" method="GET" data-filter-ajax action="{{ route('resident.lost-found.index') }}">
        @if ($status !== '')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="search-box grow">
            <input class="input" type="search" name="q" value="{{ old('q', $q) }}" placeholder="Search item, description or place…">
        </div>

        <button class="btn btn-primary" type="submit"><x-icon name="search" /> Search</button>

        @if ($status !== '' || $q !== '')
            <a href="{{ route('resident.lost-found.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    @if ($items->isEmpty())
        <div class="card">
            <div class="empty">
                <div class="ico"><x-icon name="box" size="26" /></div>
                <h3>{{ $q || $status ? 'Nothing matches that search' : 'The board is empty' }}</h3>
                <p>{{ $q || $status ? 'Try a broader keyword or switch tabs.' : 'Post a lost item report or share something you picked up around the barangay.' }}</p>
                <button class="btn btn-primary" type="button" data-modal-open="reportModal">
                    <x-icon name="plus" /> Report an item
                </button>
            </div>
        </div>
    @else
        <div class="grid grid-3">
            @foreach ($items as $item)
                <div class="card glow">
                    <div class="card-body">
                        <div class="row between mb-1">
                            <span class="badge {{ $badge($item->status) }}">{{ $item->statusLabel() }}</span>
                            <span class="chip">{{ $item->categoryName() }}</span>
                        </div>

                        <h3>{{ $item->item_name }}</h3>

                        <p class="small muted">{{ \Illuminate\Support\Str::limit($item->description, 140) }}</p>

                        <div class="detail-list">
                            <div class="d"><dt>Where</dt><dd>{{ $item->location }}</dd></div>
                            <div class="d"><dt>Date</dt><dd>{{ $item->date_occurred?->format('M d, Y') ?? '—' }}</dd></div>
                            <div class="d"><dt>Posted</dt><dd>{{ $item->created_at->diffForHumans() }}</dd></div>
                        </div>

                        <div class="pin-note mt-2">
                            <x-icon name="phone" size="13" /> {{ $item->contact_info }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($items->hasPages())
        @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
        @php($window = range(max(1, $items->currentPage() - 2), max(1, min($items->lastPage(), $items->currentPage() + 2))))
        <nav class="pagination" aria-label="Pagination">
            @if ($items->onFirstPage())
                <span>&laquo;</span>
            @else
                <a href="{{ $items->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach ($window as $p)
                @if ($p === $items->currentPage())
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}">&raquo;</a>
            @else
                <span>&raquo;</span>
            @endif
        </nav>
    @endif

    {{-- ============ report modal ============ --}}
    @push('modals')
        <div class="modal" id="reportModal">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-card">
                <div class="modal-head">
                    <h3 class="card-title"><x-icon name="box" /> Report an item</h3>
                    <button class="modal-x" type="button" data-modal-close aria-label="Close">&times;</button>
                </div>

                <form method="POST" action="{{ route('resident.lost-found.store') }}">
                    @csrf

                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="field span-2">
                                <label class="label" for="item_name">Item name <span class="req">*</span></label>
                                <input class="input" id="item_name" name="item_name" maxlength="120"
                                       value="{{ old('item_name') }}" placeholder="e.g. Black wallet, iPhone 12, House keys with blue tag">
                                @error('item_name')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label" for="category">Category <span class="req">*</span></label>
                                <select class="select" id="category" name="category">
                                    <option value="">Choose…</option>
                                    @foreach ($categories as $key => $label)
                                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label" for="status">Report type <span class="req">*</span></label>
                                <select class="select" id="status" name="status">
                                    <option value="lost" @selected(old('status') === 'lost')>I lost this item</option>
                                    <option value="found" @selected(old('status') === 'found')>I found this item</option>
                                </select>
                                @error('status')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field span-2">
                                <label class="label" for="description">Description <span class="req">*</span></label>
                                <textarea class="textarea" id="description" name="description" rows="3" maxlength="1000"
                                          placeholder="Color, brand, contents, distinguishing marks…">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label" for="location">Where <span class="req">*</span></label>
                                <input class="input" id="location" name="location" maxlength="190"
                                       value="{{ old('location') }}" placeholder="e.g. Covered court, Purok 2">
                                @error('location')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label" for="date_occurred">Date <span class="req">*</span></label>
                                <input class="input" id="date_occurred" name="date_occurred" type="date"
                                       max="{{ now()->toDateString() }}" value="{{ old('date_occurred') }}">
                                @error('date_occurred')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field span-2">
                                <label class="label" for="contact_info">How to reach you <span class="req">*</span></label>
                                <input class="input" id="contact_info" name="contact_info" maxlength="120"
                                       value="{{ old('contact_info', auth()->user()->phone ?? '') }}" placeholder="Mobile number or email">
                                <div class="help">Shown on the board so claimants can reach you.</div>
                                @error('contact_info')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-foot">
                        <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
                        <button class="btn btn-primary" type="submit">
                            <x-icon name="send" /> Publish report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var hasErrors = {{ $errors->any() ? 'true' : 'false' }};
        var modal = document.getElementById('reportModal');

        if (hasErrors && modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    });
</script>
@endpush
