@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'My emergency reports — '.config('app.name'))
@section('topbar-title', 'Emergency Assistance')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>My emergency reports</h1></div>
        <a href="{{ route('resident.emergency.create') }}" class="btn btn-danger">Report an incident</a>
    </div>

    <section class="card">
        @if ($reports->isEmpty())
            <div class="empty"><h3>No reports yet</h3><p>You haven't submitted any emergency reports.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Category</th><th>Location</th><th>Status</th><th>Filed</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($reports as $r)
                            <tr>
                                <td>{{ $r->reference_no }}</td>
                                <td>{{ $r->categoryName() }}</td>
                                <td>{{ $r->location }}</td>
                                <td><span class="badge badge-{{ $r->status === 'resolved' ? 'green' : 'rose' }}">{{ $r->statusLabel() }}</span></td>
                                <td>{{ $r->created_at->format('M j, Y') }}</td>
                                <td><a href="{{ route('resident.emergency.show', $r) }}" class="btn btn-sm btn-info">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $reports->links() }}</div>
        @endif
    </section>
@endsection