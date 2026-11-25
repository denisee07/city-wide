@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Census report — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Census report</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card" style="padding: 1.5rem;">
        <div class="table-wrap">
            <table class="table" style="width: 100%;">
                <thead><tr><th>Name</th><th>Sex</th><th>Age</th><th>Barangay</th><th>Purok</th><th>Household</th></tr></thead>
                <tbody>
                    @forelse ($records as $r)
                        <tr>
                            <td>{{ $r->full_name }}</td>
                            <td>{{ ucfirst($r->sex ?? '—') }}</td>
                            <td>{{ $r->age ?? '—' }}</td>
                            <td>{{ $r->barangay?->name ?? '—' }}</td>
                            <td>{{ $r->purok ?? '—' }}</td>
                            <td>{{ $r->household_no ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center" style="padding: 3rem 1.5rem; color: var(--dim);">
                                No census records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;" class="small dim">
            <span>Total: {{ $records->count() }} records</span>
            <span>Generated {{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </section>
@endsection