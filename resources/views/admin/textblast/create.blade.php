@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'New text blast — '.config('app.name'))
@section('topbar-title', 'Text Blast')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">SMS</span><h1>Compose text blast</h1></div>
        <a href="{{ route('admin.textblast.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:760px">
        <form method="POST" action="{{ route('admin.textblast.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label>Scope</label>
                    <select name="scope" class="input">
                        <option value="citywide">Citywide</option>
                        <option value="barangay">Barangay</option>
                    </select>
                </div>
                <div class="field">
                    <label>Barangay (if scoped)</label>
                    <select name="barangay_id" class="input">
                        <option value="">—</option>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Recipient group</label>
                    <select name="recipient_group" class="input">
                        <option value="all">All users</option>
                        <option value="residents">Residents only</option>
                        <option value="officials">Officials only</option>
                    </select>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Message *</label>
                    <textarea name="message" class="input" rows="5" required maxlength="1600"></textarea>
                </div>
            </div>
            <div class="row" style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">Send blast</button>
            </div>
        </form>
    </section>
@endsection