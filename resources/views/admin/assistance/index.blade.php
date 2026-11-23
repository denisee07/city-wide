@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Assistance programs — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>Assistance programs</h1></div>
        <div class="row">
            <a href="{{ route('admin.assistance.scan') }}" class="btn btn-info">Scan QR</a>
            <a href="{{ route('admin.assistance.create') }}" class="btn btn-primary">New program</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><div class="label">Programs</div><div class="value">{{ $stats['programs'] }}</div></div>
        <div class="stat"><div class="label">Beneficiaries</div><div class="value">{{ $stats['beneficiaries'] }}</div></div>
        <div class="stat"><div class="label">Claimed</div><div class="value">{{ $stats['claimed'] }}</div></div>
        <div class="stat"><div class="label">Pending</div><div class="value">{{ $stats['pending'] }}</div></div>
    </div>

    <section class="card">
        @if ($programs->isEmpty())
            <div class="empty"><h3>No programs</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Type</th><th>Distribution</th><th>Barangay</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($programs as $p)
                            <tr>
                                <td>{{ $p->title }}</td>
                                <td>{{ AssistanceProgram::types()[$p->type] ?? $p->type }}</td>
                                <td>{{ optional($p->distribution_date)->format('M j, Y') }} {{ $p->distribution_time }}</td>
                                <td>{{ $p->barangay?->name ?? 'Citywide' }}</td>
                                <td>{{ AssistanceProgram::statuses()[$p->status] ?? $p->status }}</td>
                                <td><a href="{{ route('admin.assistance.show', $p) }}" class="btn btn-sm btn-info">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $programs->links() }}</div>
        @endif
    </section>
@endsection