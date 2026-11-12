@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Queue control board — '.config('app.name'))
@section('topbar-title', 'Queueing')

@section('content')
    @php
        $actions = \App\Http\Controllers\Admin\QueueController::ACTIONS;
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Live board · {{ now()->format('F j, Y') }}</span>
            <h1>Queue control board</h1>
            <p class="sub">Call, serve and close tickets as residents arrive at the window.</p>
        </div>
        <div class="row">
            <form method="POST" action="{{ route('admin.queue.reset') }}"
                data-confirm="Clear every ticket issued today? This cannot be undone.">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <x-icon name="refresh" size="15" /> Reset day
                </button>
            </form>
            <button type="button" class="btn btn-primary btn-lg" data-modal-open="callNext">
                <x-icon name="megaphone" size="16" /> Call next resident
            </button>
        </div>
    </div>

    {{-- ==================== board + line ==================== --}}
    <div class="grid grid-23 mb-3">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="ticket" /> Now serving</h2>
                <span class="row tiny dim" style="gap:.4rem">
                    <x-icon name="clock" size="13" /> <span data-clock>—</span>
                </span>
            </div>
            <div class="card-body">
                <div class="board" id="queueBoard">
                    @include('admin.queue.partials.board')
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="users" /> Next in line</h2>
                <span class="badge badge-cyan" id="nextBadge">{{ $waiting->count() }} waiting</span>
            </div>
            <div class="card-body stack-sm">
                <p class="tiny dim mb-0">Tickets are pulled in the order they were issued — 5 minute average per transaction.</p>

                <div class="stack-sm" id="nextList">
                    @include('admin.queue.partials.next-list')
                </div>

                {{-- The "Call next resident" button in the page head opens this
                     same modal. Two buttons, one action, both on screen at once
                     - the duplicate here is the one that went. --}}
            </div>
        </section>
    </div>

    {{-- ==================== waiting + serving ==================== --}}
    <div class="grid grid-2 mb-3">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="clock" /> Waiting</h2>
                <span class="badge badge-cyan" id="waitingBadge">{{ $waiting->count() }} queued</span>
            </div>
            <div class="card-body stack-sm">
                <div class="stack-sm" id="waitingList">
                    @include('admin.queue.partials.waiting-list')
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="check-circle" /> At the window</h2>
                <span class="badge badge-amber" id="activeBadge">{{ $active->count() }} in progress</span>
            </div>
            <div class="card-body stack-sm">
                <div class="stack-sm" id="activeList">
                    @include('admin.queue.partials.active-list')
                </div>
            </div>
        </section>
    </div>

    {{-- ==================== finished + walk-in ==================== --}}
    <div class="grid grid-2">
        <section class="card" id="finishedCard">
            @include('admin.queue.partials.finished')
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="plus" /> Add a walk-in</h2>
                <span class="badge badge-violet">Counter</span>
            </div>

            <form method="POST" action="{{ route('admin.queue.store') }}">
                @csrf
                <div class="card-body">
                    <p class="small muted">
                        For residents who arrive without a phone — issue a ticket right at the desk and
                        the board updates instantly.
                    </p>

                    <div class="field">
                        <label class="label" for="walkin_name">Name on ticket <span class="req">*</span></label>
                        <input id="walkin_name" type="text" name="walkin_name" class="input" maxlength="120"
                            placeholder="e.g. Maria Santos" value="{{ old('walkin_name') }}">
                        @error('walkin_name')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="walkin_service">Service needed <span class="req">*</span></label>
                        <select id="walkin_service" name="walkin_service" class="select">
                            @foreach ($services as $key => $label)
                                <option value="{{ $key }}" @selected(old('walkin_service') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('walkin_service')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-info mb-0">
                        <x-icon name="info" size="17" />
                        <div class="small mb-0">
                            The next ticket number is
                            <strong class="mono">{{ \App\Models\QueueTicket::nextNumber(today()->toDateString()) }}</strong>.
                        </div>
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Walk-ins join the same line as online requests.</span>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="ticket" size="15" /> Issue ticket
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- ==================== call next modal ==================== --}}
    <div class="modal" id="callNext">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Queue</div>
                    <h3 class="mb-0">Call the next resident</h3>
                </div>
                <button type="button" class="modal-x" data-modal-close>&times;</button>
            </div>

            <div class="modal-body stack">
                <p class="small muted mb-0">
                    The oldest waiting ticket is pulled, announced at the selected window and moved
                    to <strong>at the window</strong>.
                </p>

                <div class="field">
                    <label class="label" for="callWindow">Window</label>
                    <select id="callWindow" class="select">
                        @foreach ($windows as $window)
                            <option value="{{ $window }}">{{ $window }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="alert alert-info mb-0">
                    <x-icon name="ticket" size="17" />
                    <div class="small mb-0">
                        <span id="callQueueCount">{{ $waiting->count() }}</span> resident(s) waiting
                        · currently serving <strong>{{ $nowServing?->ticket_no ?? 'nobody' }}</strong>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                <button type="button" class="btn btn-primary" id="callNextBtn">
                    <x-icon name="megaphone" size="15" /> Call now
                </button>
            </div>
        </div>
    </div>

    {{-- row template moved into the "at the window" list after a call --}}
    <template id="activeRowTpl">
        <div class="queue-row">
            <span class="qn" data-f="ticket_no"></span>
            <span class="grow">
                <span data-f="name"></span>
                <span class="tiny dim"> · <span data-f="service"></span></span>
            </span>
            <span class="badge badge-amber">Now serving</span>
            <span class="row shrink-0" style="gap:.35rem">
                <form method="POST" data-act>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="done">
                    <button type="submit" class="btn btn-sm btn-success">Done</button>
                </form>
                <form method="POST" data-act>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="skipped">
                    <button type="submit" class="btn btn-sm btn-ghost">Skip</button>
                </form>
            </span>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('callNextBtn');
            if (!btn) return;

            var base = '{{ route('admin.queue.index') }}';

            function setText(id, value) {
                var el = document.getElementById(id);
                if (el) el.textContent = value;
            }

            function refreshCounts(waitingCount, activeCount) {
                setText('boardWaiting', waitingCount);
                setText('boardActive', activeCount);
                setText('callQueueCount', waitingCount);
                setText('nextBadge', waitingCount + ' waiting');

                var badge = document.getElementById('waitingBadge');
                if (badge) badge.textContent = waitingCount + ' queued';
            }

            btn.addEventListener('click', function () {
                btn.disabled = true;

                fetch('{{ route('admin.queue.call-next') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ window: document.getElementById('callWindow').value })
                })
                    .then(function (res) {
                        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
                    })
                    .then(function (res) {
                        btn.disabled = false;

                        if (!res.ok) {
                            toast(res.data.message || 'Nobody is waiting in line.', 'warning');
                            return;
                        }

                        var t = res.data.ticket;

                        setText('boardNow', res.data.now_serving);
                        setText('boardWin', res.data.window || 'Window 1');
                        refreshCounts(res.data.waiting_count, parseInt(document.getElementById('boardActive').textContent || '0', 10) + 1);

                        var waitingRow = document.getElementById('wt-' + t.id);
                        if (waitingRow) waitingRow.remove();

                        var nextRow = document.getElementById('next-' + t.id);
                        if (nextRow) nextRow.remove();

                        var nextList = document.getElementById('nextList');
                        if (nextList && nextList.querySelectorAll('.queue-row').length === 0) {
                            var nextMsg = document.getElementById('nextEmpty');
                            if (nextMsg) nextMsg.hidden = false;
                        }

                        var waitingList = document.getElementById('waitingList');
                        if (waitingList) {
                            var rows = waitingList.querySelectorAll('.queue-row');
                            if (rows.length === 0) {
                                var msg = document.getElementById('waitingEmpty');
                                if (msg) msg.hidden = false;
                            }
                            rows.forEach(function (row, i) {
                                var pos = row.querySelector('.grow .tiny');
                                if (pos) {
                                    pos.textContent = pos.textContent.replace(/#\d+ in line/, '#' + (i + 1) + ' in line');
                                }
                            });
                        }

                        var tpl = document.getElementById('activeRowTpl');
                        var activeList = document.getElementById('activeList');
                        if (tpl && activeList) {
                            var node = tpl.content.cloneNode(true);
                            node.querySelector('[data-f="ticket_no"]').textContent = t.ticket_no;
                            node.querySelector('[data-f="name"]').textContent = t.name_on_ticket;
                            node.querySelector('[data-f="service"]').textContent = t.service_name || t.service;
                            node.querySelector('.queue-row').id = 'st-' + t.id;

                            node.querySelectorAll('form[data-act]').forEach(function (form) {
                                form.action = base + '/' + t.id;
                            });

                            var emptyMsg = document.getElementById('activeEmpty');
                            if (emptyMsg) emptyMsg.hidden = true;

                            activeList.prepend(node);
                        }

                        toast('Now serving ' + res.data.now_serving + ' at ' + (res.data.window || 'Window 1') + '.', 'success');

                        var modal = document.getElementById('callNext');
                        if (modal) modal.classList.remove('open');
                        document.body.style.overflow = '';
                    })
                    .catch(function () {
                        btn.disabled = false;
                        toast('Could not reach the queue service. Try again.', 'error');
                    });
            });

            // keep the board counters in sync with tickets issued elsewhere
            window.pollUrl('{{ url('/api/queue/status') }}', function (data) {
                if (!data || !Array.isArray(data.tickets)) return;

                var waiting = 0;
                var active = 0;
                var done = 0;
                var skipped = 0;

                data.tickets.forEach(function (t) {
                    if (t.status === 'waiting') waiting++;
                    else if (t.status === 'called' || t.status === 'serving') active++;
                    else if (t.status === 'done') done++;
                    else if (t.status === 'skipped' || t.status === 'no_show') skipped++;
                });

                setText('boardNow', data.now_serving || '—');
                // A ticket on screen must not be paired with "No ticket called yet".
                setText('boardWin', data.window
                    || (data.now_serving ? 'Window not assigned' : 'No ticket called yet'));
                setText('boardServed', done);
                setText('boardSkipped', skipped);
                refreshCounts(waiting, active);
            }, 15000);
        });
    </script>
@endpush
