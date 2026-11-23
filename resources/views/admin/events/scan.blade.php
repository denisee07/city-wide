@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Scan attendance — '.config('app.name'))
@section('topbar-title', 'Events')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Events</span><h1>Scan attendance</h1></div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:600px">
        <form method="POST" action="{{ route('admin.events.scan.verify') }}">
            @csrf
            <div class="field"><label>QR code value</label><input type="text" name="code" class="input" value="{{ old('code') }}" autofocus></div>
            @error('code')<p class="text-danger small">{{ $message }}</p>@enderror
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </section>

    @isset($reg)
        <section class="card" style="max-width:600px;margin-top:1rem">
            <h3>Registration</h3>
            <div class="kv">
                <div><span class="k">Resident</span><span class="v">{{ $reg->resident?->name }}</span></div>
                <div><span class="k">Event</span><span class="v">{{ $reg->event?->title }}</span></div>
                <div><span class="k">Reference</span><span class="v">{{ $reg->reference_no }}</span></div>
                <div><span class="k">Status</span><span class="v">{{ $reg->status }}</span></div>
            </div>
            @if ($reg->status !== 'attended')
                <form method="POST" action="{{ route('admin.events.attend', $reg) }}" style="margin-top:1rem">
                    @csrf
                    <button type="submit" class="btn btn-primary">Mark attended</button>
                </form>
            @else
                <p class="small dim" style="margin-top:1rem">Already attended on {{ $reg->attended_at?->format('M j, Y g:i A') }}.</p>
            @endif
        </section>
    @endisset
@endsection