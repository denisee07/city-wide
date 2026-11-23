@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Text blast — '.config('app.name'))
@section('topbar-title', 'Text Blast')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">SMS</span><h1>Text blast history</h1></div>
        <a href="{{ route('admin.textblast.create') }}" class="btn btn-primary">New blast</a>
    </div>

    <section class="card">
        @if ($blasts->isEmpty())
            <div class="empty"><h3>No blasts yet</h3></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>When</th><th>Scope</th><th>Group</th><th>Recipients</th><th>Status</th><th>Message</th></tr></thead>
                    <tbody>
                        @foreach ($blasts as $b)
                            <tr>
                                <td>{{ optional($b->sent_at)->format('M j, Y g:i A') }}</td>
                                <td>{{ $b->scope }} {{ $b->barangay?->name }}</td>
                                <td>{{ $b->recipient_group }}</td>
                                <td>{{ $b->recipient_count }}</td>
                                <td>{{ $b->status }}</td>
                                <td class="small">{{ \Illuminate\Support\Str::limit($b->message, 60) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $blasts->links() }}</div>
        @endif
    </section>
@endsection