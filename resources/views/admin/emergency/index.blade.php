@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Emergency monitoring — '.config('app.name'))
@section('topbar-title', 'Emergency Monitoring')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>Emergency monitoring</h1><p class="sub">Incoming reports from residents.</p></div>
        <a href="{{ route('admin.emergency.advisories') }}" class="btn btn-primary">Manage advisories</a>
    </div>

    <div class="stats">
        <div class="stat"><div class="label">Total</div><div class="value">{{ $stats['total'] }}</div></div>
        <div class="stat"><div class="label">Active</div><div class="value">{{ $stats['active'] }}</div></div>
        <div class="stat"><div class="label">Critical</div><div class="value">{{ $stats['critical'] }}</div></div>
        <div class="stat"><div class="label">Resolved</div><div class="value">{{ $stats['resolved'] }}</div></div>
    </div>

    <form method="GET" action="{{ route('admin.emergency.index') }}">
        <div class="toolbar">
            <select name="barangay" class="input">
                <option value="">All barangays</option>
                @foreach ($barangays as $b)
                    <option value="{{ $b->id }}" @selected(request('barangay')==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="category" class="input">
                <option value="">All categories</option>
                @foreach (\App\Models\EmergencyReport::categories() as $k => $v)
                    <option value="{{ $k }}" @selected(request('category')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="status" class="input">
                <option value="">All statuses</option>
                @foreach (\App\Models\EmergencyReport::statuses() as $k => $v)
                    <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>

    <section class="card">
        @if ($reports->isEmpty())
            <div class="empty"><h3>No reports</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Reference</th><th>Reporter</th><th>Category</th><th>Barangay</th><th>Priority</th><th>Status</th><th>Filed</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($reports as $r)
                            <tr>
                                <td>{{ $r->reference_no }}</td>
                                <td>{{ $r->reporter?->name }}</td>
                                <td>{{ $r->categoryName() }}</td>
                                <td>{{ $r->barangay?->name }}</td>
                                <td><span class="badge badge-{{ $r->priority === 'critical' ? 'rose' : 'neutral' }}">{{ ucfirst($r->priority) }}</span></td>
                                <td><span class="badge">{{ $r->statusLabel() }}</span></td>
                                <td>{{ $r->created_at->format('M j') }}</td>
                                <td><a href="{{ route('admin.emergency.show', $r) }}" class="btn btn-sm btn-info">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $reports->links() }}</div>
        @endif
    </section>

    <section class="card" style="margin-top:1.5rem">
        <div class="card-head"><h2>Recent advisories</h2></div>
        @foreach ($advisories as $a)
            <div class="row" style="padding:.5rem 0;border-bottom:1px solid var(--border)">
                <div class="grow">
                    <strong>{{ $a->title }}</strong>
                    <span class="tiny dim"> · {{ $a->is_critical ? 'CRITICAL' : '' }} {{ $a->scope }}</span>
                </div>
                <span class="tiny dim">{{ optional($a->published_at)->format('M j') }}</span>
            </div>
        @endforeach
    </section>
@endsection