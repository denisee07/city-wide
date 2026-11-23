@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Barangays — '.config('app.name'))
@section('topbar-title', 'Barangays')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">City-wide breakdown</span>
            <h1>All 18 barangays</h1>
            <p class="sub">Workload side by side, busiest first. Pick a row to open that barangay's boards.</p>
        </div>
        <div class="row">
            <span class="badge badge-violet">{{ $totals['barangays'] }} barangays</span>
            <span class="badge badge-rose">{{ $totals['open'] }} open cases</span>
            @if ($totals['urgent'] > 0)
                <span class="badge badge-rose">{{ $totals['urgent'] }} urgent</span>
            @endif
        </div>
    </div>

    {{-- ============ city totals ============ --}}
    <div class="grid grid-4 mb-3">
        @foreach ([
            ['label' => 'Open complaints', 'value' => $totals['open'],       'tone' => 'rose',   'icon' => 'alert'],
            ['label' => 'Document requests', 'value' => $totals['docs'],    'tone' => 'violet', 'icon' => 'file'],
            ['label' => 'Appointments',     'value' => $totals['appts'],     'tone' => 'amber',  'icon' => 'calendar'],
            ['label' => 'Waiting in queue', 'value' => $totals['waiting'],   'tone' => 'cyan',   'icon' => 'ticket'],
        ] as $stat)
            <div class="card">
                <div class="card-body">
                    <div class="row between items-center">
                        <span class="tiny dim">{{ $stat['label'] }}</span>
                        <x-icon name="{{ $stat['icon'] }}" size="16" />
                    </div>
                    <div class="bold" style="font-size:1.7rem;margin-top:.35rem">{{ $stat['value'] }}</div>
                    <div class="tiny dim">across {{ $totals['barangays'] }} barangays</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============ per-barangay breakdown ============ --}}
    <section class="card">
        <div class="card-head">
            <h2 class="card-title"><x-icon name="grid" /> Workload by barangay</h2>
            <span class="tiny dim">{{ $totals['residents'] }} registered residents city-wide</span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th>Open cases</th>
                        <th>Urgent</th>
                        <th>Docs</th>
                        <th>Appointments</th>
                        <th>Queue</th>
                        <th>Residents</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr @if ($focusId === $row['id']) style="background:rgba(34,211,238,.06)" @endif>
                            <td>
                                <div class="row gap-1">
                                    <span class="badge badge-neutral">{{ $row['id'] }}</span>
                                    <button type="button" class="btn btn-ghost btn-sm" data-modal-open="brgy-preview-{{ $row['id'] }}">
                                        <span class="bold">{{ $row['name'] }}</span>
                                    </button>
                                </div>
                                @if ($row['alias'])
                                    <div class="tiny dim">{{ $row['alias'] }}</div>
                                @endif
                            </td>

                            {{-- Load bar, so a heavy barangay is obvious at a glance --}}
                            <td>
                                <div class="row gap-1">
                                    <span class="bold">{{ $row['open'] }}</span>
                                    <span style="display:inline-block;width:64px;height:6px;border-radius:99px;background:var(--border);overflow:hidden">
                                        <span style="display:block;height:100%;width:{{ (int) round($row['open'] / $maxOpen * 100) }}%;background:var(--accent)"></span>
                                    </span>
                                </div>
                            </td>

                            <td>
                                @if ($row['urgent'] > 0)
                                    <span class="badge badge-rose">{{ $row['urgent'] }}</span>
                                @else
                                    <span class="tiny dim">0</span>
                                @endif
                            </td>

                            <td>{{ $row['docs'] }}</td>
                            <td>{{ $row['appts'] }}</td>
                            <td>{{ $row['waiting'] }}</td>
                            <td>{{ $row['residents'] }}</td>

                            <td>
                                {{-- nowrap, otherwise the flex row wraps and each
                                     button takes its own line, quadrupling the
                                     row height on a narrow viewport. --}}
                                <div style="display:flex;gap:.35rem;justify-content:flex-end;flex-wrap:nowrap;white-space:nowrap">
                                    <a href="{{ route('admin.complaints.index', ['barangay' => $row['id']]) }}"
                                       class="btn btn-ghost btn-sm">Cases</a>
                                    <a href="{{ route('admin.requests.index', ['barangay' => $row['id']]) }}"
                                       class="btn btn-ghost btn-sm">Docs</a>
                                    <a href="{{ route('admin.queue.index', ['barangay' => $row['id']]) }}"
                                       class="btn btn-ghost btn-sm">Queue</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="center dim" style="padding:2rem">No barangays configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach ($rows as $row)
        @php $b = \App\Models\Barangay::find($row['id']); @endphp
        <div class="modal" id="brgy-preview-{{ $row['id'] }}" data-modal>
            <div class="modal-card" style="max-width:640px">
                <div class="modal-head">
                    <h3>{{ $b?->name }} <span class="tiny dim">{{ $b?->alias }}</span></h3>
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="kv">
                        <div><span class="k">Population (2020)</span><span class="v">{{ number_format($b?->population_2020 ?? 0) }}</span></div>
                        <div><span class="k">Registered residents</span><span class="v">{{ $row['residents'] }}</span></div>
                        <div><span class="k">Census records</span><span class="v">{{ \App\Models\CensusRecord::where('barangay_id', $row['id'])->count() }}</span></div>
                        <div><span class="k">Open complaints</span><span class="v">{{ $row['open'] }} ({{ $row['urgent'] }} urgent)</span></div>
                        <div><span class="k">Pending docs</span><span class="v">{{ $row['docs'] }}</span></div>
                        <div><span class="k">Appointments</span><span class="v">{{ $row['appts'] }}</span></div>
                        <div><span class="k">Queue waiting</span><span class="v">{{ $row['waiting'] }}</span></div>
                        <div><span class="k">Announcements</span><span class="v">{{ \App\Models\Announcement::where('barangay_id', $row['id'])->count() }}</span></div>
                        <div><span class="k">Active assistance programs</span><span class="v">{{ \App\Models\AssistanceProgram::where('barangay_id', $row['id'])->where('status','active')->count() }}</span></div>
                        <div><span class="k">Upcoming events</span><span class="v">{{ \App\Models\Event::where('barangay_id', $row['id'])->where('status','upcoming')->count() }}</span></div>
                        <div><span class="k">Emergency reports (active)</span><span class="v">{{ \App\Models\EmergencyReport::where('barangay_id', $row['id'])->whereIn('status',['submitted','received','under_review','responding'])->count() }}</span></div>
                    </div>
                    <div class="row" style="margin-top:1rem;gap:.5rem">
                        <a href="{{ route('admin.complaints.index', ['barangay' => $row['id']]) }}" class="btn btn-ghost btn-sm">Cases</a>
                        <a href="{{ route('admin.requests.index', ['barangay' => $row['id']]) }}" class="btn btn-ghost btn-sm">Docs</a>
                        <a href="{{ route('admin.queue.index', ['barangay' => $row['id']]) }}" class="btn btn-ghost btn-sm">Queue</a>
                        <a href="{{ route('admin.emergency.index', ['barangay' => $row['id']]) }}" class="btn btn-ghost btn-sm">Emergency</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <p class="tiny dim mt-3">
        Every row opens the same boards the barangay officials use, filtered to that barangay.
        Use the scope badge in the top bar to clear the filter.
    </p>
@endsection
