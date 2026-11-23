@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', $program->title.' — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>{{ $program->title }}</h1></div>
        <a href="{{ route('resident.assistance.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card">
        <div class="kv">
            <div><span class="k">Type</span><span class="v">{{ \App\Models\AssistanceProgram::types()[$program->type] ?? $program->type }}</span></div>
            <div><span class="k">Distribution</span><span class="v">{{ optional($program->distribution_date)->format('M j, Y') }} {{ $program->distribution_time }}</span></div>
            <div><span class="k">Location</span><span class="v">{{ $program->location ?? '—' }}</span></div>
            <div><span class="k">Status</span><span class="v">{{ \App\Models\AssistanceProgram::statuses()[$program->status] ?? $program->status }}</span></div>
        </div>
        @if ($program->description)<p style="margin-top:1rem">{{ $program->description }}</p>@endif
        @if ($program->eligibility)<p class="small dim">Eligibility: {{ $program->eligibility }}</p>@endif
    </section>

    <section class="card" style="margin-top:1rem">
        <h3>Your beneficiary status</h3>
        @if (!$beneficiary)
            <p class="dim">You are not listed as a beneficiary for this program.</p>
        @else
            <div class="kv">
                <div><span class="k">Reference</span><span class="v">{{ $beneficiary->reference_no }}</span></div>
                <div><span class="k">Status</span><span class="v">{{ $beneficiary->status }}</span></div>
                @if ($beneficiary->claimed_at)<div><span class="k">Claimed</span><span class="v">{{ $beneficiary->claimed_at->format('M j, Y g:i A') }}</span></div>@endif
            </div>
            <div style="margin-top:1rem">
                <h4>Your QR code</h4>
                <code style="display:block;padding:.75rem;background:#000;border-radius:6px;word-break:break-all">{{ $beneficiary->qr_code }}</code>
                <p class="tiny dim">Present this code at the distribution counter.</p>
            </div>
        @endif
    </section>
@endsection