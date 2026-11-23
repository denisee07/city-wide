@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', $report->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Emergency Monitoring')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>{{ $report->reference_no }}</h1><p class="sub">{{ $report->categoryName() }} · {{ $report->location }}</p></div>
        <a href="{{ route('admin.emergency.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <div class="grid-2">
        <section class="card">
            <h3>Report details</h3>
            <div class="kv">
                <div><span class="k">Reporter</span><span class="v">{{ $report->reporter?->name }} ({{ $report->reporter?->phone }})</span></div>
                <div><span class="k">Category</span><span class="v">{{ $report->categoryName() }}</span></div>
                <div><span class="k">Barangay</span><span class="v">{{ $report->barangay?->name }}</span></div>
                <div><span class="k">Destination</span><span class="v">{{ $report->destination === 'city' ? 'City Hall / CDRRMO' : 'Barangay' }}</span></div>
                <div><span class="k">Priority</span><span class="v">{{ ucfirst($report->priority) }}</span></div>
                <div><span class="k">Status</span><span class="v">{{ $report->statusLabel() }}</span></div>
                <div><span class="k">Filed</span><span class="v">{{ $report->created_at->format('M j, Y g:i A') }}</span></div>
            </div>
            <div style="margin-top:1rem"><h4>Description</h4><p>{{ $report->description }}</p></div>
            @if ($report->photo)
                <div style="margin-top:1rem"><img src="{{ asset('storage/'.$report->photo) }}" alt="" style="max-width:100%;border-radius:8px"></div>
            @endif
        </section>

        <section class="card">
            <h3>Update report</h3>
            <form method="POST" action="{{ route('admin.emergency.update', $report) }}">
                @csrf @method('PATCH')
                <div class="field">
                    <label>Status</label>
                    <select name="status" class="input">
                        @foreach (\App\Models\EmergencyReport::statuses() as $k => $v)
                            <option value="{{ $k }}" @selected($report->status===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Priority</label>
                    <select name="priority" class="input">
                        @foreach (['low','medium','high','critical'] as $p)
                            <option value="{{ $p }}" @selected($report->priority===$p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Assign to</label>
                    <select name="assigned_to" class="input">
                        <option value="">— unassigned —</option>
                        @foreach ($officials as $o)
                            <option value="{{ $o->id }}" @selected($report->assigned_to===$o->id)>{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Response notes</label>
                    <textarea name="response_notes" class="input" rows="4">{{ old('response_notes', $report->response_notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save update</button>
            </form>
        </section>
    </div>
@endsection