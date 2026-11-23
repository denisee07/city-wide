@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', $report->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Emergency Assistance')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>{{ $report->reference_no }}</h1><p class="sub">{{ $report->categoryName() }} · {{ $report->location }}</p></div>
        <a href="{{ route('resident.emergency.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card">
        <div class="kv">
            <div><span class="k">Status</span><span class="v">{{ $report->statusLabel() }}</span></div>
            <div><span class="k">Priority</span><span class="v">{{ ucfirst($report->priority) }}</span></div>
            <div><span class="k">Destination</span><span class="v">{{ $report->destination === 'city' ? 'City Hall / CDRRMO' : 'Barangay Administration' }}</span></div>
            <div><span class="k">Barangay</span><span class="v">{{ $report->barangay?->name }}</span></div>
            <div><span class="k">Filed</span><span class="v">{{ $report->created_at->format('M j, Y g:i A') }}</span></div>
            @if ($report->acknowledged_at)
                <div><span class="k">Acknowledged</span><span class="v">{{ $report->acknowledged_at->format('M j, Y g:i A') }}</span></div>
            @endif
            @if ($report->resolved_at)
                <div><span class="k">Resolved</span><span class="v">{{ $report->resolved_at->format('M j, Y g:i A') }}</span></div>
            @endif
        </div>
        <div style="margin-top:1rem">
            <h3>Description</h3>
            <p>{{ $report->description }}</p>
        </div>
        @if ($report->response_notes)
            <div style="margin-top:1rem">
                <h3>Response notes</h3>
                <p>{{ $report->response_notes }}</p>
            </div>
        @endif
        @if ($report->photo)
            <div style="margin-top:1rem">
                <h3>Photo</h3>
                <img src="{{ asset('storage/'.$report->photo) }}" alt="incident photo" style="max-width:100%;border-radius:8px">
            </div>
        @endif
    </section>
@endsection