@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'New assistance program — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>New assistance program</h1></div>
        <a href="{{ route('admin.assistance.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:760px">
        <form method="POST" action="{{ route('admin.assistance.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field" style="grid-column:1/-1">
                    <label>Title *</label>
                    <input type="text" name="title" class="input" required>
                </div>
                <div class="field">
                    <label>Type</label>
                    <select name="type" class="input">
                        @foreach (\App\Models\AssistanceProgram::types() as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status" class="input">
                        @foreach (\App\Models\AssistanceProgram::statuses() as $k => $v)
                            <option value="{{ $k }}" @selected($k==='draft')>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Distribution date</label>
                    <input type="date" name="distribution_date" class="input">
                </div>
                <div class="field">
                    <label>Distribution time</label>
                    <input type="text" name="distribution_time" class="input">
                </div>
                <div class="field">
                    <label>Location</label>
                    <input type="text" name="location" class="input">
                </div>
                <div class="field">
                    <label>Barangay (blank = citywide)</label>
                    <select name="barangay_id" class="input">
                        <option value="">Citywide</option>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Beneficiary limit</label>
                    <input type="number" name="beneficiary_limit" class="input" min="1">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Description</label>
                    <textarea name="description" class="input" rows="3"></textarea>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Eligibility</label>
                    <textarea name="eligibility" class="input" rows="2"></textarea>
                </div>
            </div>
            <div class="row" style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">Create program</button>
            </div>
        </form>
    </section>
@endsection