@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Scan QR — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>Scan beneficiary QR</h1></div>
        <a href="{{ route('admin.assistance.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:600px">
        <form method="POST" action="{{ route('admin.assistance.scan.verify') }}">
            @csrf
            <div class="field">
                <label>QR code value</label>
                <input type="text" name="code" class="input" value="{{ old('code') }}" autofocus>
            </div>
            @error('code')<p class="text-danger small">{{ $message }}</p>@enderror
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </section>

    @isset($ben)
        <section class="card" style="max-width:600px;margin-top:1rem">
            <h3>Beneficiary</h3>
            <div class="kv">
                <div><span class="k">Resident</span><span class="v">{{ $ben->resident?->name }}</span></div>
                <div><span class="k">Program</span><span class="v">{{ $ben->program?->title }}</span></div>
                <div><span class="k">Reference</span><span class="v">{{ $ben->reference_no }}</span></div>
                <div><span class="k">Status</span><span class="v">{{ $ben->status }}</span></div>
            </div>
            @if ($ben->status !== 'claimed')
                <form method="POST" action="{{ route('admin.assistance.claim', $ben) }}" style="margin-top:1rem">
                    @csrf
                    <button type="submit" class="btn btn-primary">Confirm claim</button>
                </form>
            @else
                <p class="small dim" style="margin-top:1rem">Already claimed on {{ $ben->claimed_at?->format('M j, Y g:i A') }}.</p>
            @endif
        </section>
    @endisset
@endsection