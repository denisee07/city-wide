@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Emergency report — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Emergency incident report</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card" style="padding: 1.5rem;">
        <div class="table-wrap">
            <table class="table" style="width: 100%;">
                <thead><tr><th>Reference</th><th>Reporter</th><th>Category</th><th>Barangay</th><th>Priority</th><th>Status</th><th>Filed</th></tr></thead>
                <tbody>
                    @forelse ($items as $r)
                        <tr>
                            <td>{{ $r->reference_no }}</td>
                            <td>{{ $r->reporter?->name }}</td>
                            <td>{{ $r->categoryName() }}</td>
                            <td>{{ $r->barangay?->name }}</td>
                            <td>{{ ucfirst($r->priority) }}</td>
                            <td>{{ $r->statusLabel() }}</td>
                            <td>{{ $r->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="center" style="padding: 3rem 1.5rem; color: var(--dim);">
                                No emergency reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;" class="small dim">
            <span>Total: {{ $items->count() }} records</span>
            <span>Generated {{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </section>
@endsection