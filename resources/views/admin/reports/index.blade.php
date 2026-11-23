@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Reports — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Printable reports</h1><p class="sub">Generate, download and print administrative reports.</p></div>
    </div>

    <div class="grid-2">
        <a href="{{ route('admin.reports.census') }}" class="card" style="text-decoration:none"><h3>Census report</h3><p class="small dim">Population and household records.</p></a>
        <a href="{{ route('admin.reports.residents') }}" class="card" style="text-decoration:none"><h3>Resident registration list</h3><p class="small dim">Registered residents.</p></a>
        <a href="{{ route('admin.reports.beneficiaries') }}" class="card" style="text-decoration:none"><h3>Beneficiary list</h3><p class="small dim">Assistance beneficiaries and QR codes.</p></a>
        <a href="{{ route('admin.reports.attendance') }}" class="card" style="text-decoration:none"><h3>Event attendance</h3><p class="small dim">Event registrations and attendance.</p></a>
        <a href="{{ route('admin.reports.emergency') }}" class="card" style="text-decoration:none"><h3>Emergency incident report</h3><p class="small dim">Emergency reports and status.</p></a>
    </div>
@endsection