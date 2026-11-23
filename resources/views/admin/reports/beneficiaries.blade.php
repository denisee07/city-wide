@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Beneficiary list — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Beneficiary list</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <form method="GET" action="{{ route('admin.reports.beneficiaries') }}" class="toolbar">
        <select name="program" class="input">
            <option value="">All programs</option>
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @selected(optional($program)->id===$p->id)>{{ $p->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Resident</th><th>Barangay</th><th>Program</th><th>Reference</th><th>Status</th><th>QR</th></tr></thead>
                <tbody>
                    @foreach ($items as $b)
                        <tr>
                            <td>{{ $b->resident?->name }}</td>
                            <td>{{ $b->resident?->barangay?->name }}</td>
                            <td>{{ $b->program?->title }}</td>
                            <td>{{ $b->reference_no }}</td>
                            <td>{{ $b->status }}</td>
                            <td class="tiny">{{ $b->qr_code }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small dim" style="margin-top:1rem">Total: {{ $items->count() }}. Generated {{ now()->format('M j, Y g:i A') }}.</p>
    </section>
@endsection