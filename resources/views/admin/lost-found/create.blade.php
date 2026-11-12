@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Add lost & found item — '.config('app.name'))
@section('topbar-title', 'Add item')

@section('content')
    @php
        $categories = \App\Models\LostFound::CATEGORIES;
        $statuses = \App\Models\LostFound::STATUSES;
        $statusBadge = ['lost' => 'badge-rose', 'found' => 'badge-green', 'claimed' => 'badge-violet'];
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Lost &amp; found</span>
            <h1>Add an item</h1>
            <p class="sub">Log something a resident lost or handed in at the barangay hall.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.lost-found.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to lost &amp; found
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.lost-found.store') }}">
        @csrf

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="box" /> Item details</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="label" for="item_name">Item name <span class="req">*</span></label>
                            <input id="item_name" type="text" name="item_name" class="input" maxlength="140"
                                placeholder="e.g. Black wallet with brown strap" value="{{ old('item_name') }}">
                            @error('item_name')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="category">Category <span class="req">*</span></label>
                            <select id="category" name="category" class="select">
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category', 'others') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="status">Status <span class="req">*</span></label>
                            <select id="status" name="status" class="select">
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', 'lost') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="location">Where it happened <span class="req">*</span></label>
                            <input id="location" type="text" name="location" class="input" maxlength="190"
                                placeholder="e.g. Covered court, Riverside Purok" value="{{ old('location') }}">
                            @error('location')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="date_occurred">Date it happened</label>
                            <input id="date_occurred" type="date" name="date_occurred" class="input"
                                value="{{ old('date_occurred') }}">
                            @error('date_occurred')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="description">Description <span class="req">*</span></label>
                            <textarea id="description" name="description" class="textarea" rows="5"
                                placeholder="Colour, markings, contents, distinguishing features…">{{ old('description') }}</textarea>
                            @error('description')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Residents can claim items at the barangay hall.</span>
                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Save item
                        </button>
                    </div>
                </div>
            </section>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="camera" /> Photo &amp; contact</h2>
                    </div>
                    <div class="card-body">
                        <div class="field">
                            <label class="label" for="image">Photo link</label>
                            <input id="image" type="url" name="image" class="input" maxlength="400"
                                placeholder="https://…" value="{{ old('image') }}">
                            <div class="help">Paste a public image URL — the photo is shown on the resident board.</div>
                            @error('image')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="contact_info">Claim contact</label>
                            <input id="contact_info" type="text" name="contact_info" class="input" maxlength="80"
                                placeholder="e.g. (02) 8123-4567 / desk officer" value="{{ old('contact_info') }}">
                            <div class="help">Who residents should call to arrange a claim.</div>
                            @error('contact_info')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </section>

                <section class="card pad">
                    <span class="eyebrow">Reminder</span>
                    <p class="small muted mt-1 mb-0">
                        Mark items as <strong>claimed</strong> once released — that keeps the board honest
                        for everyone still looking.
                    </p>
                </section>
            </div>
        </div>
    </form>
@endsection
