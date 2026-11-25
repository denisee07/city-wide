@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Reports — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Printable reports</h1><p class="sub">Generate, download and print administrative reports.</p></div>
    </div>

    <div style="max-width: 900px;">
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="display: flex; flex-direction: column;">
                @foreach([
                    ['route' => route('admin.reports.census'), 'title' => 'Census report', 'desc' => 'Population and household records.'],
                    ['route' => route('admin.reports.residents'), 'title' => 'Resident registration list', 'desc' => 'Registered residents.'],
                    ['route' => route('admin.reports.beneficiaries'), 'title' => 'Beneficiary list', 'desc' => 'Assistance beneficiaries and QR codes.'],
                    ['route' => route('admin.reports.attendance'), 'title' => 'Event attendance', 'desc' => 'Event registrations and attendance.'],
                    ['route' => route('admin.reports.emergency'), 'title' => 'Emergency incident report', 'desc' => 'Emergency reports and status.'],
                ] as $index => $item)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; {{ $index > 0 ? 'border-top: 1px solid var(--border);' : '' }}">
                        <div>
                            <h3 style="margin-bottom: 0.25rem; font-size: 1.05rem;">{{ $item['title'] }}</h3>
                            <p class="small dim" style="margin: 0;">{{ $item['desc'] }}</p>
                        </div>
                        <a href="{{ $item['route'] }}" class="badge report-btn" style="background: rgba(34,211,238,0.08); border: 1px solid var(--primary); color: var(--primary); font-size: 0.75rem; text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease;">
                            CLICK TO VIEW &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .report-btn:hover {
            background: var(--primary) !important;
            color: var(--bg) !important;
        }
    </style>
@endsection
