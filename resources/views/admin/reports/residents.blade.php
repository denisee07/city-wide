@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Resident list — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Resident registration list</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Barangay</th><th>Purok</th><th>Registered</th></tr></thead>
                <tbody>
                    @foreach ($residents as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone }}</td>
                            <td>{{ $u->barangay?->name ?? '—' }}</td>
                            <td>{{ $u->purok }}</td>
                            <td>{{ $u->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small dim" style="margin-top:1rem">Total: {{ $residents->count() }}. Generated {{ now()->format('M j, Y g:i A') }}.</p>
    </section>
@endsection