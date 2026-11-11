<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QueueController extends Controller
{
    use ScopesAdminQueries;

    /** The single display board on the wall plus the per-window counters. */
    public const WINDOWS = ['Window 1', 'Window 2', 'Window 3', 'Window 4'];

    /**
     * Which buttons each ticket status offers, as [label, target status, class].
     * Shared by the board and by the ajax patch that rewrites a row, so a
     * freshly rendered row offers exactly the same actions as a full load.
     */
    public const ACTIONS = [
        'waiting' => [
            ['Call', 'called', 'btn-primary'],
            ['Skip', 'skipped', 'btn-ghost'],
            ['No show', 'no_show', 'btn-ghost'],
        ],
        'called' => [
            ['Start', 'serving', 'btn-primary'],
            ['Done', 'done', 'btn-success'],
            ['Skip', 'skipped', 'btn-ghost'],
        ],
        'serving' => [
            ['Done', 'done', 'btn-success'],
            ['No show', 'no_show', 'btn-ghost'],
        ],
    ];

    /** Status -> badge class, shared by the board and the in-place patch. */
    public const STATUS_BADGES = [
        'waiting' => 'badge-cyan',
        'called' => 'badge-amber',
        'serving' => 'badge-amber',
        'done' => 'badge-green',
        'skipped' => 'badge-rose',
        'no_show' => 'badge-rose',
    ];

    public function index(): View
    {
        return view('admin.queue.index', $this->boardData());
    }

    /**
     * Everything the board renders, gathered once so the first load and an
     * ajax refresh of the same regions are computed by identical code.
     *
     * @return array<string, mixed>
     */
    private function boardData(): array
    {
        $today = $this->scoped(QueueTicket::forDate());

        $nowServing = (clone $today)
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->first();

        $waiting = (clone $today)->where('status', 'waiting')->orderBy('id')->get();
        $active = (clone $today)
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->get();
        $finished = (clone $today)
            ->whereIn('status', ['done', 'skipped', 'no_show'])
            ->orderByDesc('served_at')
            ->orderByDesc('id')
            ->get();

        return [
            'nowServing' => $nowServing,
            'waiting' => $waiting,
            'active' => $active,
            'finished' => $finished,
            'nextInLine' => $waiting->take(5),
            'windows' => self::WINDOWS,
            'services' => QueueTicket::SERVICES,
            'statuses' => QueueTicket::STATUSES,
            'scopeLabel' => $this->scopeLabel(),
            'actions' => self::ACTIONS,
            'badge' => self::STATUS_BADGES,
            'doneCount' => $finished->where('status', 'done')->count(),
            'skippedCount' => $finished->whereIn('status', ['skipped', 'no_show'])->count(),
        ];
    }

    /** Issue a ticket for a resident who walks up to the counter. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'walkin_name' => ['required', 'string', 'max:120'],
            'walkin_service' => ['required', Rule::in(array_keys(QueueTicket::SERVICES))],
        ]);

        $ticket = QueueTicket::create([
            'ticket_no' => QueueTicket::nextNumber(today()->toDateString()),
            'name_on_ticket' => trim($data['walkin_name']),
            'service' => $data['walkin_service'],
            'status' => 'waiting',
            'queue_date' => today()->toDateString(),
            // A walk-in has no owning resident, so the issuing official's own
            // barangay is what puts the slip on the right board.
            'barangay_id' => $this->defaultBarangayId(),
        ]);

        return redirect()
            ->route('admin.queue.index')
            ->with('success', $ticket->ticket_no.' was issued to '.$ticket->name_on_ticket.'.');
    }

    /** Pull the oldest waiting ticket, announce it, and return the fresh board state. */
    public function callNext(Request $request): JsonResponse
    {
        $data = $request->validate([
            'window' => ['nullable', 'string', 'max:40'],
        ]);

        $ticket = $this->scoped(QueueTicket::forDate())
            ->where('status', 'waiting')
            ->orderBy('id')
            ->first();

        if (! $ticket) {
            return response()->json([
                'message' => 'Nobody is waiting in line right now.',
            ], 404);
        }

        $ticket->update([
            'status' => 'called',
            'called_at' => $ticket->called_at ?? now(),
            /* 'window' is nullable, so the key is absent from $data when not posted. */
            'window' => ($data['window'] ?? null) ?: 'Window 1',
        ]);

        $waitingCount = $this->scoped(QueueTicket::forDate())->where('status', 'waiting')->count();

        $payload = $ticket->fresh()->toArray();
        $payload['service_name'] = $ticket->serviceName();
        $payload['status_label'] = $ticket->statusLabel();

        return response()->json([
            'ticket' => $payload,
            'now_serving' => $ticket->ticket_no,
            'window' => $ticket->window,
            'waiting_count' => $waitingCount,
        ]);
    }

    public function update(Request $request, QueueTicket $queueTicket): RedirectResponse|JsonResponse
    {
        $this->guardInScope($queueTicket);

        $data = $request->validate([
            'status' => ['required', Rule::in(['called', 'serving', 'done', 'skipped', 'no_show'])],
            'window' => ['nullable', 'string', 'max:40'],
        ]);

        $queueTicket->status = $data['status'];

        if ($data['status'] === 'called') {
            $queueTicket->called_at = $queueTicket->called_at ?? now();

            if ($request->filled('window')) {
                $queueTicket->window = $data['window'];
            }
        }

        if ($data['status'] === 'done') {
            $queueTicket->served_at = now();
        }

        $queueTicket->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ticket' => $queueTicket,
                'waiting_count' => $this->scoped(QueueTicket::forDate())->where('status', 'waiting')->count(),
                'message' => $queueTicket->ticket_no.' is now "'.$queueTicket->statusLabel().'".',
                'patches' => $this->boardPatches(),
            ]);
        }

        return redirect()
            ->route('admin.queue.index')
            ->with('success', $queueTicket->ticket_no.' is now "'.$queueTicket->statusLabel().'".');
    }

    /**
     * Every board region a ticket action can move: the summary counts, the
     * peek at the line, the waiting list, the window list and the finished
     * table. A ticket changes which of these it belongs to, so an in-place
     * update has to refresh all of them together rather than patch one badge.
     *
     * @return array<int, array{selector: string, html: string}>
     */
    private function boardPatches(): array
    {
        $data = $this->boardData();

        return [
            ['selector' => '#queueBoard', 'html' => view('admin.queue.partials.board', $data)->render()],
            ['selector' => '#nextList', 'html' => view('admin.queue.partials.next-list', $data)->render()],
            ['selector' => '#waitingList', 'html' => view('admin.queue.partials.waiting-list', $data)->render()],
            ['selector' => '#activeList', 'html' => view('admin.queue.partials.active-list', $data)->render()],
            ['selector' => '#finishedCard', 'html' => view('admin.queue.partials.finished', $data)->render()],
            ['selector' => '#waitingBadge', 'html' => $data['waiting']->count().' queued'],
            ['selector' => '#activeBadge', 'html' => $data['active']->count().' in progress'],
            ['selector' => '#nextBadge', 'html' => $data['waiting']->count().' waiting'],
        ];
    }

    /** Wipe every ticket issued today and start the board fresh. */
    public function reset(): RedirectResponse
    {
        $removed = $this->scoped(QueueTicket::forDate())->delete();

        return redirect()
            ->route('admin.queue.index')
            ->with('success', 'Queue reset — '.$removed.' ticket(s) cleared from today\'s board.');
    }

    /**
     * A barangay counter acts only on the slips its own barangay issued. The
     * city admin sees the combined board and may act on any of them.
     */
    private function guardInScope(QueueTicket $queueTicket): void
    {
        $id = $this->scopeBarangayId();

        if ($id === null) {
            return; // city admin
        }

        $ticketBarangay = $queueTicket->barangay_id
            ?? $queueTicket->resident?->barangay_id;

        if ($ticketBarangay !== $id) {
            abort(404);
        }
    }
}
