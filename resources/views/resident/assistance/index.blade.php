@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'Assistance programs — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>Available assistance programs</h1></div>
    </div>

    <section class="card">
        @if ($programs->isEmpty())
            <div class="empty"><h3>No active programs</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Title</th><th>Type</th><th>Distribution</th><th>Location</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($programs as $p)
                            <tr>
                                <td>{{ $p->title }}</td>
                                <td>{{ \App\Models\AssistanceProgram::types()[$p->type] ?? $p->type }}</td>
                                <td>{{ optional($p->distribution_date)->format('M j, Y') }} {{ $p->distribution_time }}</td>
                                <td>{{ $p->location ?? '—' }}</td>
                                <td><a href="{{ route('resident.assistance.show', $p) }}" class="btn btn-sm btn-info">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $programs->links() }}</div>
        @endif
    </section>
@endsection