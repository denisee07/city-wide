@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Edit announcement — '.config('app.name'))
@section('topbar-title', 'Edit announcement')

@section('content')
    @php($categories = \App\Http\Controllers\Admin\AnnouncementController::CATEGORIES)

    <div class="page-head">
        <div>
            <span class="eyebrow">Publishing</span>
            <h1>Edit announcement</h1>
            <p class="sub">Changes go live on the resident portal as soon as you save.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.announcements.preview', $announcement) }}" class="btn btn-ghost">
                <x-icon name="eye" size="15" /> Preview
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost">Back to list</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
        @csrf
        @method('PATCH')

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="megaphone" /> Content</h2>
                    <span class="badge {{ $announcement->is_published ? 'badge-green' : 'badge-cyan' }}">
                        {{ $announcement->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="field">
                        <label class="label" for="title">Title <span class="req">*</span></label>
                        <input id="title" type="text" name="title" class="input" maxlength="190"
                            placeholder="Announcement title"
                            value="{{ old('title', $announcement->title) }}">
                        @error('title')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="body">Body <span class="req">*</span></label>
                        <textarea id="body" name="body" class="textarea" rows="9"
                            placeholder="What is happening, who it is for, what residents should bring…">{{ old('body', $announcement->body) }}</textarea>
                        <div class="help">At least 10 characters. Line breaks are preserved on the portal.</div>
                        @error('body')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Last updated {{ $announcement->updated_at->diffForHumans() }}</span>
                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Save changes
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
                                    <option value="{{ $key }}" @selected(old('category', $announcement->category) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="event_date">Event date</label>
                            <input id="event_date" type="text" name="event_date" class="input" maxlength="80"
                                placeholder="e.g. August 8, 2026" value="{{ old('event_date', $announcement->event_date) }}">
                            @error('event_date')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="location">Location</label>
                            <input id="location" type="text" name="location" class="input" maxlength="190"
                                placeholder="e.g. Barangay Hall covered court" value="{{ old('location', $announcement->location) }}">
                            @error('location')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <hr class="divider">

                        <label class="check">
                            <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned))>
                            <span>Pin to the top of every announcement list</span>
                        </label>
                        @error('is_pinned')<div class="error">{{ $message }}</div>@enderror

                        <label class="check">
                            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $announcement->is_published))>
                            <span>Publish to the resident portal</span>
                        </label>
                        @error('is_published')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </section>

                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="trash" /> Danger zone</h2>
                    </div>
                    <div class="card-body">
                        <p class="small muted">
                            Posted {{ $announcement->created_at->format('M j, Y') }}
                            by {{ $announcement->creator?->name ?? 'Barangay office' }}.
                            Deleting removes the bulletin everywhere.
                        </p>
                        <button type="button" class="btn btn-danger btn-block btn-sm" data-modal-open="deleteAnnouncement">
                            <x-icon name="trash" size="14" /> Delete announcement
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection

@push('modals')
    <div class="modal" id="deleteAnnouncement">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h3>Delete this announcement?</h3>
                <button type="button" class="modal-x" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <p class="muted small mb-0">
                    <strong>{{ $announcement->title }}</strong> will be removed from the resident portal
                    and the public website. This cannot be undone.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Keep it</button>
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <x-icon name="trash" size="15" /> Delete permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
@endpush
