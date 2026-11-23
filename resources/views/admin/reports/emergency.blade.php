@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Emergency report — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Emergency incident report</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Reference</th><th>Reporter</th><th>Category</th><th>Barangay</th><th>Priority</th><th>Status</th><th>Filed</th></tr></thead>
                <tbody>
                    @foreach ($items as $r)
                        <tr>
                            <td>{{ $r->reference_no }}</td>
                            <td>{{ $r->reporter?->name }}</td>
                            <td>{{ $r->categoryName() }}</td>
                            <td>{{ $r->barangay?->name }}</td>
                            <td>{{ ucfirst($r->priority) }}</td>
                            <td>{{ $r->statusLabel() }}</td>
                            <td>{{ $r->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small dim" style="margin-top:1rem">Total: {{ $items->count() }}. Generated {{ now()->format('M j, Y g:i A') }}.</p>
    </section>
@endsection