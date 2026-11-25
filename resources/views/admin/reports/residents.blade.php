@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Resident list — '.config('app.name'))
@section('topbar-title', 'Printables & Reports')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Reports</span><h1>Resident registration list</h1></div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>

    <section class="card" style="padding: 1.5rem;">
        <div class="table-wrap">
            <table class="table" style="width: 100%;">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Barangay</th><th>Purok</th><th>Registered</th></tr></thead>
                <tbody>
                    @forelse ($residents as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone }}</td>
                            <td>{{ $u->barangay?->name ?? '—' }}</td>
                            <td>{{ $u->purok }}</td>
                            <td>{{ $u->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center" style="padding: 3rem 1.5rem; color: var(--dim);">
                                No residents found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;" class="small dim">
            <span>Total: {{ $residents->count() }} records</span>
            <span>Generated {{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </section>
@endsection