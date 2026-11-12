<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersStatusPatches;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use RendersStatusPatches, ScopesAdminQueries;

    /** Status -> badge class. The single map the board and the patch share. */
    public const STATUS_BADGES = [
        'pending' => 'badge-cyan',
        'confirmed' => 'badge-amber',
        'completed' => 'badge-green',
        'cancelled' => 'badge-rose',
        'no_show' => 'badge-rose',
    ];

    public function index(Request $request): View
    {
        $query = $this->scopedByResident(Appointment::query())
            ->with(['resident:id,name,email,avatar,purok', 'processor:id,name']);

        if (in_array($request->input('status'), array_keys(Appointment::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($q) use ($term) {
                $q->where('reference_no', 'like', '%'.$term.'%')
                    ->orWhere('subject', 'like', '%'.$term.'%')
                    ->orWhere('office', 'like', '%'.$term.'%')
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', '%'.$term.'%'));
            });
        }

        $appointments = $query
            ->orderBy('appointment_date')
            ->orderBy('time_slot')
            ->get()
            ->groupBy(fn ($a) => $a->appointment_date->toDateString());

        $counts = $this->scopedByResident(Appointment::query())
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'counts' => $counts,
            'statuses' => Appointment::STATUSES,
            'scopeLabel' => $this->scopeLabel(),
            'badge' => self::STATUS_BADGES,
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $this->guardInScope($appointment);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'remarks' => ['nullable', 'string', 'max:4000'],
        ]);

        $appointment->update([
            'status' => $data['status'],
            'remarks' => ($data['remarks'] ?? null) ?: null,
            'processed_by' => $request->user()?->id,
        ]);

        return $this->savedResponse(
            $request,
            'admin.appointments.index',
            $appointment->reference_no.' is now "'.$appointment->statusLabel().'".',
            [
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-status-cell]', $appointment->id),
                    'badge' => self::STATUS_BADGES[$appointment->status] ?? 'badge-neutral',
                    'label' => $appointment->statusLabel(),
                    'remark' => $appointment->remarks,
                ],
                /* Re-render this booking's own form so it agrees with what was
                   just saved. Left alone, the selects inside would still hold
                   the pre-save status and a second press of Save would quietly
                   put it back. */
                [
                    'selector' => sprintf('[data-manage-slot="%d"]', $appointment->id),
                    'view' => 'admin.appointments.partials.manage-modal',
                    'data' => [
                        'booking' => $appointment,
                        'statuses' => Appointment::STATUSES,
                    ],
                ],
            ],
        );
    }

    /** As with documents: scope follows the resident who booked it. */
    private function guardInScope(Appointment $appointment): void
    {
        $id = $this->scopeBarangayId();

        if ($id !== null && $appointment->resident?->barangay_id !== $id) {
            abort(404);
        }
    }
}
