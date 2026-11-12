@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'New job post — '.config('app.name'))
@section('topbar-title', 'New job post')

@section('content')
    @php
        $categories = \App\Models\Job::CATEGORIES;
        $types = \App\Models\Job::TYPES;
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Livelihood</span>
            <h1>New job post</h1>
            <p class="sub">Share an opening with residents looking for work.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to jobs
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.jobs.store') }}">
        @csrf

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="briefcase" /> Job details</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="label" for="title">Job title <span class="req">*</span></label>
                            <input id="title" type="text" name="title" class="input" maxlength="160"
                                placeholder="e.g. Construction helper (with lodging)" value="{{ old('title') }}">
                            @error('title')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="company">Employer / company <span class="req">*</span></label>
                            <input id="company" type="text" name="company" class="input" maxlength="140"
                                placeholder="e.g. Santa Rosa Builders" value="{{ old('company') }}">
                            @error('company')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="location">Work location <span class="req">*</span></label>
                            <input id="location" type="text" name="location" class="input" maxlength="140"
                                placeholder="e.g. Quezon City / on-site" value="{{ old('location') }}">
                            @error('location')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="category">Category <span class="req">*</span></label>
                            <select id="category" name="category" class="select">
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category', 'general') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="employment_type">Employment type <span class="req">*</span></label>
                            <select id="employment_type" name="employment_type" class="select">
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('employment_type', 'full_time') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('employment_type')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="salary">Salary / compensation</label>
                            <input id="salary" type="text" name="salary" class="input" maxlength="80"
                                placeholder="e.g. ₱550 per day" value="{{ old('salary') }}">
                            @error('salary')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="deadline">Application deadline</label>
                            <input id="deadline" type="date" name="deadline" class="input" value="{{ old('deadline') }}">
                            @error('deadline')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="description">Description <span class="req">*</span></label>
                            <textarea id="description" name="description" class="textarea" rows="5"
                                placeholder="Day-to-day work, schedule, benefits, who should apply…">{{ old('description') }}</textarea>
                            @error('description')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="requirements">Requirements</label>
                            <textarea id="requirements" name="requirements" class="textarea" rows="4"
                                placeholder="Documents, experience, skills to bring to the interview…">{{ old('requirements') }}</textarea>
                            @error('requirements')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Posts appear on the resident portal once saved.</span>
                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Publish post
                        </button>
                    </div>
                </div>
            </section>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="phone" /> Contact &amp; state</h2>
                    </div>
                    <div class="card-body stack">
                        <div class="field">
                            <label class="label" for="contact_person">Contact person</label>
                            <input id="contact_person" type="text" name="contact_person" class="input" maxlength="120"
                                placeholder="e.g. Ms. Reyes" value="{{ old('contact_person') }}">
                            @error('contact_person')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="contact_number">Contact number</label>
                            <input id="contact_number" type="text" name="contact_number" class="input" maxlength="40"
                                placeholder="e.g. 0917 123 4567" value="{{ old('contact_number') }}">
                            @error('contact_number')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label class="label" for="contact_email">Contact e-mail</label>
                            <input id="contact_email" type="email" name="contact_email" class="input" maxlength="190"
                                placeholder="hiring@example.com" value="{{ old('contact_email') }}">
                            @error('contact_email')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <hr class="divider">

                        <div class="field">
                            <label class="label" for="status">Status <span class="req">*</span></label>
                            <select id="status" name="status" class="select">
                                <option value="open" @selected(old('status', 'open') === 'open')>Open — accepting applicants</option>
                                <option value="closed" @selected(old('status') === 'closed')>Closed — no longer hiring</option>
                            </select>
                            @error('status')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <label class="check">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))>
                            <span>Feature this post at the top of the board</span>
                        </label>
                        @error('is_featured')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection
