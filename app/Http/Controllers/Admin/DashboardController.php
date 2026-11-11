<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Job;
use App\Models\QueueTicket;
use App\Models\User;
use App\Support\BarangayFilter;
use App\Support\CityBreakdown;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ScopesAdminQueries;

    public function index(): View
    {
        /* ---- complaint breakdown for the bar chart ---- */
        $statusTotals = $this->scoped(Complaint::query())
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $statusTotals->sum();
        $peak = max(1, (int) $statusTotals->max());

        $bars = collect(ComplaintController::STATUSES)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'badge' => ComplaintController::STATUS_BADGES[$key],
                'count' => (int) ($statusTotals[$key] ?? 0),
                'pct' => (int) round(((int) ($statusTotals[$key] ?? 0) / $peak) * 100),
            ])
            ->values();

        $peakBar = $bars->sortByDesc('count')->first() ?? [
            'key' => 'received',
            'label' => ComplaintController::STATUSES['received'] ?? 'Received',
            'count' => 0,
        ];

        /* ---- headline numbers ---- */
        $openComplaints = $this->scoped(Complaint::query())
            ->whereIn('status', ['received', 'in_progress', 'on_hold'])
            ->count();
        $pendingDocs = $this->scopedByResident(DocumentRequest::query())
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
        $pendingAppts = $this->scopedByResident(Appointment::query())
            ->where('status', 'pending')
            ->count();

        $todayQueue = $this->scopedByResident(QueueTicket::forDate());
        $waiting = (clone $todayQueue)->where('status', 'waiting')->count();
        $residents = $this->scopedUsers(User::query())->where('role', 'resident')->count();
        $openJobs = $this->scopedVisible(Job::query())->where('status', 'open')->count();

        /* ---- today's queue snapshot ---- */
        $nowServing = (clone $todayQueue)
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->first();
        $servedToday = (clone $todayQueue)->where('status', 'done')->count();
        $skippedToday = (clone $todayQueue)->whereIn('status', ['skipped', 'no_show'])->count();
        $queueLine = (clone $todayQueue)->where('status', 'waiting')->orderBy('id')->take(3)->get();

        /* ---- recent activity ---- */
        $latestComplaints = $this->scoped(Complaint::query())
            ->with(['resident:id,name,purok', 'assignee:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $announcements = $this->scopedVisible(Announcement::query()->published())
            ->with('creator:id,name')
            ->take(4)
            ->get();

        $urgent = $this->scoped(Complaint::query())->where('priority', 'urgent')
            ->whereIn('status', ['received', 'in_progress', 'on_hold'])
            ->count();

        /* What an official should act on first, in priority order.

           The dashboard otherwise opens with a bar chart and six numbers,
           which is a lot to decode on a first visit. This strip answers the
           only question a newcomer has - "where do I start?" - and every tile
           links to the list already filtered to that work.

           $scope carries whatever the console is already showing, so a tile never
           contradicts the scope pill above. */
        $scope = BarangayFilter::params();

        $attention = [
            [
                'label' => 'complaints need a first look',
                'count' => $openComplaints,
                'hot'   => $urgent > 0,
                'hint'  => $urgent > 0 ? $urgent.' urgent' : null,
                'href'  => route('admin.complaints.index', $scope + ['status' => 'received']),
            ],
            [
                'label' => 'document requests awaiting review',
                'count' => $pendingDocs,
                'href'  => route('admin.requests.index', $scope + ['status' => 'pending']),
            ],
            [
                'label' => 'appointments awaiting confirmation',
                'count' => $pendingAppts,
                'href'  => route('admin.appointments.index', $scope + ['status' => 'pending']),
            ],
            [
                'label' => 'residents waiting in the queue',
                'count' => $waiting,
                'href'  => route('admin.queue.index', $scope),
            ],
        ];

        /* Headline numbers are a flat sum across the barangays on screen, which
           hides where the work actually is. The pulse strip breaks that sum
           back down so a heavy barangay is visible without leaving the
           dashboard. It is pointless once the console is already narrowed to
           one barangay, so it only shows when more than one is in view. */
        $cityPulse = null;

        if ($this->effectiveBarangayId() === null) {
            $breakdown = CityBreakdown::perBarangay();

            $cityPulse = [
                'rows'    => $breakdown->take(6)->values(),
                'maxOpen' => CityBreakdown::maxOpen($breakdown),
                'totals'  => CityBreakdown::totals($breakdown),
            ];
        }

        return view('admin.dashboard', [
            'bars' => $bars,
            'peakBar' => $peakBar,
            'total' => $total,
            'attention' => $attention,
            'urgent' => $urgent,
            'cityPulse' => $cityPulse,
            'scopeLabel' => $this->scopeLabel(),
            'stats' => [
                [
                    'label' => 'Open complaints',
                    'value' => $openComplaints,
                    'tone' => 'rose',
                    'meta' => $urgent.' urgent waiting for action',
                    'icon' => 'alert',
                    'href' => route('admin.complaints.index', $scope + ['status' => 'received']),
                ],
                [
                    'label' => 'Document requests',
                    'value' => $pendingDocs,
                    'tone' => 'violet',
                    'meta' => 'Pending review at the records desk',
                    'icon' => 'file',
                    'href' => route('admin.requests.index', $scope + ['status' => 'pending']),
                ],
                [
                    'label' => 'Appointments',
                    'value' => $pendingAppts,
                    'tone' => 'amber',
                    'meta' => 'Awaiting confirmation',
                    'icon' => 'calendar',
                    'href' => route('admin.appointments.index', $scope + ['status' => 'pending']),
                ],
                [
                    'label' => 'Waiting in queue',
                    'value' => $waiting,
                    'tone' => 'cyan',
                    'meta' => 'Residents served today: '.$servedToday,
                    'icon' => 'ticket',
                    'href' => route('admin.queue.index', $scope),
                ],
                [
                    'label' => 'Registered residents',
                    'value' => $residents,
                    'tone' => 'green',
                    'meta' => 'Accounts in the community roster',
                    'icon' => 'users',
                    'href' => route('admin.users.index', $scope + ['role' => 'resident']),
                ],
                [
                    'label' => 'Open job posts',
                    'value' => $openJobs,
                    'tone' => 'blue',
                    'meta' => 'Livelihood listings live now',
                    'icon' => 'briefcase',
                    'href' => route('admin.jobs.index', $scope + ['status' => 'open']),
                ],
            ],
            'nowServing' => $nowServing,
            'waiting' => $waiting,
            'servedToday' => $servedToday,
            'skippedToday' => $skippedToday,
            'queueLine' => $queueLine,
            'latestComplaints' => $latestComplaints,
            'announcements' => $announcements,
        ]);
    }
}
