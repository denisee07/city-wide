@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'My QR code — '.config('app.name'))
@section('topbar-title', 'My QR Code')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Identification</span><h1>My resident QR code</h1></div>
    </div>

    <section class="card" style="max-width:480px;text-align:center">
        <code style="display:block;padding:1rem;background:#000;border-radius:8px;word-break:break-all;font-size:.9rem">{{ auth()->user()->qr_code }}</code>
        <p class="small dim" style="margin-top:1rem">Present this code for verification at the barangay hall and city services.</p>
    </section>
@endsection