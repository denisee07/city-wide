@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Census report — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Census report</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Name</th><th>Sex</th><th>Age</th><th>Barangay</th><th>Purok</th><th>Household</th></tr></thead>
                <tbody>
                    @foreach ($records as $r)
                        <tr>
                            <td>{{ $r->full_name }}</td>
                            <td>{{ ucfirst($r->sex ?? '—') }}</td>
                            <td>{{ $r->age ?? '—' }}</td>
                            <td>{{ $r->barangay?->name ?? '—' }}</td>
                            <td>{{ $r->purok ?? '—' }}</td>
                            <td>{{ $r->household_no ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small dim" style="margin-top:1rem">Total: {{ $records->count() }} records. Generated {{ now()->format('M j, Y g:i A') }}.</p>
    </section>
@endsection