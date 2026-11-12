@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Lost & Found — '.config('app.name'))
@section('topbar-title', 'Lost & Found')

@section('content')
    @php
        $statusBadge = \App\Http\Controllers\Admin\LostFoundController::STATUS_BADGES;
        $statusLabels = \App\Models\LostFound::STATUSES;
        $categories = \App\Models\LostFound::CATEGORIES;
        $chips = ['all' => 'All items', 'lost' => 'Lost', 'found' => 'Found', 'claimed' => 'Claimed'];
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Publishing</span>
            <h1>Lost &amp; found board</h1>
            <p class="sub">Items reported by residents — keep statuses current so claims move fast.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.lost-found.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="16" /> Add item
            </a>
        </div>
    </div>

    {{-- ============ filters ============ --}}
    <form method="GET" data-filter-ajax action="{{ route('admin.lost-found.index') }}">
        <div class="toolbar">
            <div class="row gap-1" style="gap:.45rem">
                @foreach ($chips as $key => $label)
                    {{-- ListFilters keeps the search term when a chip is picked. --}}
                    <a href="{{ route('admin.lost-found.index', \App\Support\ListFilters::with('status', $key === 'all' ? null : $key)) }}"
                        class="chip {{ $active === $key ? 'active' : '' }}">
                        {{ $label }}
                        <span class="tiny dim">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search item, place or description…"
                    value="{{ old('q', request('q')) }}">
            </div>

            {{-- Carries the chip choice into the form so Search keeps it. --}}
            @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Search</button>

            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.lost-found.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    {{-- ============ board ============ --}}
    <section class="card">
        @if ($items->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="box" size="22" /></div>
                <h3>No items match</h3>
                <p>Nothing on the board for this filter. Add a found item so its owner can claim it.</p>
                <a href="{{ route('admin.lost-found.create') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" size="15" /> Add an item
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Where / when</th>
                            <th>Reported by</th>
                            <th>Quick status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr data-record-id="{{ $item->id }}">
                                <td class="strong">
                                    {{ $item->item_name }}
                                    <div class="tiny dim">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</div>
                                    {{-- Rewritten in place after an ajax save; see LostFoundController. --}}
                                    <span data-status-cell class="mt-1" style="display:inline-flex">
                                        @include('admin.partials.status-cell', [
                                            'badge' => $statusBadge[$item->status] ?? 'badge-neutral',
                                            'label' => $item->statusLabel(),
                                        ])
                                    </span>
                                </td>
                                <td class="nowrap">{{ $item->categoryName() }}</td>
                                <td class="nowrap">
                                    {{ $item->location }}
                                    <div class="tiny dim">{{ $item->date_occurred?->format('M j, Y') ?? 'Date not given' }}</div>
                                </td>
                                <td class="nowrap">
                                    {{ $item->reporter?->name ?? 'Barangay office' }}
                                    <div class="tiny dim">{{ $item->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <form method="POST" data-ajax action="{{ route('admin.lost-found.status', $item) }}" class="row" style="flex-wrap:nowrap; gap:.4rem">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="select" style="min-width:120px">
                                            @foreach ($statusLabels as $key => $label)
                                                <option value="{{ $key }}" @selected($item->status === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm">Set</button>
                                    </form>
                                </td>
                                <td class="right nowrap">
                                    <div class="row" style="justify-content:flex-end; gap:.4rem">
                                        <a href="{{ route('admin.lost-found.edit', $item) }}" class="btn btn-sm btn-info">
                                            <x-icon name="edit" size="14" /> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.lost-found.destroy', $item) }}"
                                            data-confirm="Remove &quot;{{ $item->item_name }}&quot; from the board?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Delete">
                                                <x-icon name="trash" size="14" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
