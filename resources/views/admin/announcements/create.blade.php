@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'New announcement — '.config('app.name'))
@section('topbar-title', 'New announcement')

@section('content')
    @php($categories = \App\Http\Controllers\Admin\AnnouncementController::CATEGORIES)

    <div class="page-head">
        <div>
            <span class="eyebrow">Publishing</span>
            <h1>New announcement</h1>
            <p class="sub">Compose a bulletin for the resident portal and public website.</p>
        </div>
        <div class="row">
            {{-- The one escape link on this page (the foot's duplicate went), so
                 it names where it actually lands instead of just "Cancel". --}}
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to announcements
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.announcements.store') }}">
        @csrf

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="megaphone" /> Content</h2>
                </div>
                <div class="card-body">
                    <div class="field">
                        <label class="label" for="title">Title <span class="req">*</span></label>
                        <input id="title" type="text" name="title" class="input" maxlength="190"
                            placeholder="e.g. Clean-up drive along Riverside Purok"
                            value="{{ old('title') }}">
                        @error('title')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="body">Body <span class="req">*</span></label>
                        <textarea id="body" name="body" class="textarea" rows="9"
                            placeholder="What is happening, who it is for, what residents should bring or prepare…">{{ old('body') }}</textarea>
                        <div class="help">At least 10 characters. Line breaks are preserved on the portal.</div>
                        @error('body')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Unpublished announcements stay hidden from residents.</span>
                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Save announcement
                        </button>
                    </div>
                </div>
            </section>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="sliders" /> Publishing options</h2>
                    </div>
                    <div class="card-body stack">
                        <div class="field">
                            <label class="label" for="category">Category <span class="req">*</span></label>
                            <select id="category" name="category" class="select">
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category', 'information') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="event_date">Event date</label>
                            <input id="event_date" type="text" name="event_date" class="input" maxlength="80"
                                placeholder="e.g. August 8, 2026" value="{{ old('event_date') }}">
                            @error('event_date')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="location">Location</label>
                            <input id="location" type="text" name="location" class="input" maxlength="190"
                                placeholder="e.g. Barangay Hall covered court" value="{{ old('location') }}">
                            @error('location')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <hr class="divider">

                        <label class="check">
                            <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))>
                            <span>Pin to the top of every announcement list</span>
                        </label>
                        @error('is_pinned')<div class="error">{{ $message }}</div>@enderror

                        <label class="check">
                            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', true))>
                            <span>Publish immediately</span>
                        </label>
                        @error('is_published')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="card pad">
                    <span class="eyebrow">Tip</span>
                    <p class="small muted mt-1 mb-0">
                        Keep titles under 60 characters — they appear in the resident dashboard card and in the
                        public site&rsquo;s announcement strip.
                    </p>
                </section>
            </div>
        </div>
    </form>
@endsection
