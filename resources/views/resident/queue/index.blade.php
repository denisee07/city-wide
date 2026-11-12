@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Queueing System — '.config('app.name'))
@section('topbar-title', 'Queueing System')

@php($badge = fn (string $s) => match ($s) { 'received', 'pending', 'waiting' => 'badge-cyan', 'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber', 'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green', 'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose', 'on_hold', 'claimed' => 'badge-violet', default => 'badge-neutral' })

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Live board</span>
            <h1>Queueing system</h1>
            <p class="sub">{{ now()->format('l, F j, Y') }} · Barangay service windows · updates every few seconds</p>
        </div>
        <div class="row">
            <span class="badge badge-cyan"><x-icon name="clock" size="13" /> <span data-clock>--:--:--</span></span>
        </div>
    </div>

    <div class="grid grid-23">
        {{-- ============ board ============ --}}
        <div class="stack">
            <div class="board">
                <div class="now-label">Now serving</div>
                <div class="now-num" id="nowNum">{{ $nowServing?->ticket_no ?? '—' }}</div>
                <div class="now-win" id="nowWin">
                    @if ($nowServing)
                        {{ $nowServing->serviceName() }}{{ $nowServing->window ? ' · Window '.$nowServing->window : '' }}
                    @else
                        Nobody called yet — take a number to get started
                    @endif
                </div>

                <div class="split">
                    <div>
                        <span>Waiting in line</span>
                        <b id="waitCount">{{ $waitingCount }}</b>
                    </div>
                    <div>
                        <span>Your number</span>
                        <b>{{ $mine?->ticket_no ?? '—' }}</b>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="users" /> Waiting list</h2>
                    <span class="badge badge-cyan"><span id="waitCount2">{{ $waitingCount }}</span> in queue</span>
                </div>
                <div class="card-body">
                    <div class="stack-sm" id="waitList">
                        @forelse ($waiting as $ticket)
                            <div class="queue-row">
                                <span class="qn">{{ $ticket->ticket_no }}</span>
                                <span class="grow">{{ $ticket->serviceName() }}</span>
                                <span class="tiny dim">{{ $ticket->created_at->format('h:i A') }}</span>
                                <span class="badge badge-cyan">Waiting</span>
                            </div>
                        @empty
                            <div class="empty" id="waitEmpty">
                                <div class="ico"><x-icon name="ticket" size="26" /></div>
                                <h3>Nobody is waiting</h3>
                                <p>The hall is clear right now — take a number and you will be served first.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ my ticket ============ --}}
        <div class="stack">
            @if ($mine)
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="ticket" /> My ticket today</h2>
                        <span class="badge {{ $badge($mine->status) }}">{{ $mine->statusLabel() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="ticket">
                            <div class="tiny dim mono">TICKET NO.</div>
                            <div class="no">{{ $mine->ticket_no }}</div>
                            <div class="small muted mt-1">{{ $mine->serviceName() }} · {{ $mine->name_on_ticket }}</div>
                            <div class="perf"></div>

                            @if ($mine->status === 'waiting')
                                <div class="row between">
                                    <div>
                                        <div class="tiny dim">Position</div>
                                        <div class="bold">#{{ $mine->positionInLine() }}</div>
                                    </div>
                                    <div>
                                        <div class="tiny dim">Est. wait</div>
                                        <div class="bold">{{ $mine->waitMinutes() }} min</div>
                                    </div>
                                    <div>
                                        <div class="tiny dim">Taken</div>
                                        <div class="bold">{{ $mine->created_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                            @elseif ($mine->status === 'called')
                                <div class="alert alert-warning mb-0">
                                    <x-icon name="bell" size="16" />
                                    <div><strong>Your number was called!</strong><p class="small mb-0">Please proceed to the service window now.</p></div>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <x-icon name="check-circle" size="16" />
                                    <div><strong>Being served.</strong><p class="small mb-0">Stay at the window until your transaction is finished.</p></div>
                                </div>
                            @endif
                        </div>

                        @if ($mine->status === 'waiting')
                            <form method="POST" action="{{ route('resident.queue.leave') }}" class="mt-2"
                                  data-confirm="Give up your place in the queue? You will need a new number.">
                                @csrf
                                <button class="btn btn-danger btn-block" type="submit">
                                    <x-icon name="x" size="15" /> Leave the queue
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="plus" /> Take a number</h2>
                        <span class="badge badge-neutral">No active ticket</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('resident.queue.store') }}">
                            @csrf

                            <div class="field">
                                <label class="label" for="service">What do you need today? <span class="req">*</span></label>
                                <select class="select" id="service" name="service">
                                    <option value="">Select a service…</option>
                                    @foreach ($services as $key => $label)
                                        <option value="{{ $key }}" @selected(old('service') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('service')
                                    <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <x-icon name="info" size="16" />
                                <div>
                                    <strong>One ticket per resident, per day.</strong>
                                    <p class="small mb-0">Your number appears on the board the moment you submit.</p>
                                </div>
                            </div>

                            <button class="btn btn-primary btn-lg btn-block" type="submit">
                                <x-icon name="ticket" /> Issue my queue number
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> How the queue runs</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Service windows</dt><dd>Window 1–3</dd></div>
                        <div class="d"><dt>Calling order</dt><dd>First in, first served</dd></div>
                        <div class="d"><dt>Estimated wait</dt><dd>~5 minutes per ticket</dd></div>
                        <div class="d"><dt>Skipped numbers</dt><dd>Called once more, then passed</dd></div>
                    </div>
                    <hr class="divider">
                    <div class="pin-note"><x-icon name="alert" size="13" /> Step away? Use “leave the queue” so others move up.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .ticket .row > div { text-align: center; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var labels = @json($services);

        var esc = function (value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        };

        window.pollUrl('{{ url('/api/queue/status') }}', function (data) {
            var num = document.getElementById('nowNum');
            var win = document.getElementById('nowWin');
            var count = document.getElementById('waitCount');
            var count2 = document.getElementById('waitCount2');
            var list = document.getElementById('waitList');

            if (num) num.textContent = data.now_serving || '—';
            if (win) win.textContent = data.window || 'Nobody called yet — take a number to get started';
            if (count) count.textContent = data.waiting_count == null ? 0 : data.waiting_count;
            if (count2) count2.textContent = data.waiting_count == null ? 0 : data.waiting_count;

            if (list && Array.isArray(data.tickets)) {
                var waiting = data.tickets.filter(function (t) { return t.status === 'waiting'; });

                if (!waiting.length) {
                    list.innerHTML = '<div class="empty">' +
                        '<div class="ico"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 9V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2a2 2 0 0 0 0 6v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-6z"/></svg></div>' +
                        '<h3>Nobody is waiting</h3>' +
                        '<p>The hall is clear right now — take a number and you will be served first.</p>' +
                        '</div>';
                    return;
                }

                list.innerHTML = waiting.map(function (t) {
                    return '<div class="queue-row">' +
                        '<span class="qn">' + esc(t.ticket_no) + '</span>' +
                        '<span class="grow">' + esc(labels[t.service] || t.service || 'Service') + '</span>' +
                        '<span class="badge badge-cyan">Waiting</span>' +
                        '</div>';
                }).join('');
            }
        }, 5000);
    });
</script>
@endpush
