@php $portal = 'resident'; @endphp
@extends('layouts.portal')

@section('title', 'Notifications — '.config('app.name'))
@section('topbar-title', 'Notifications')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Inbox</span><h1>Notifications</h1><p class="sub">{{ $unread }} unread</p></div>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-ghost">Mark all read</button></form>
    </div>

    <section class="card">
        @if ($notifications->isEmpty())
            <div class="empty"><h3>No notifications</h3></div>
        @else
            <div class="list">
                @foreach ($notifications as $n)
                    <div class="row" style="padding:.75rem 0;border-bottom:1px solid var(--border);{{ $n->isUnread() ? 'background:rgba(255,255,255,.03)' : '' }}">
                        <div class="grow">
                            <strong>{{ $n->title }}</strong>
                            @if ($n->body)<p class="small dim">{{ $n->body }}</p>@endif
                            <span class="tiny dim">{{ $n->created_at->diffForHumans() }} · {{ $n->category }}</span>
                        </div>
                        @if ($n->isUnread())
                            <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf<button class="btn btn-sm btn-info">Mark read</button></form>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="card-foot">{{ $notifications->links() }}</div>
        @endif
    </section>
@endsection