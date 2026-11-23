<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\AssistanceBeneficiary;
use App\Models\AssistanceProgram;
use App\Models\CensusRecord;
use App\Models\EmergencyReport;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ScopesAdminQueries;

    public function index()
    {
        return view('admin.reports.index');
    }

    public function census(Request $request)
    {
        $q = CensusRecord::with('barangay');
        if ($bid = $this->effectiveBarangayId()) {
            $q->where('barangay_id', $bid);
        } elseif ($request->filled('barangay') && $request->barangay !== 'all') {
            $q->where('barangay_id', $request->barangay);
        }
        $records = $q->orderBy('barangay_id')->orderBy('full_name')->get();
        return view('admin.reports.census', compact('records'));
    }

    public function residents(Request $request)
    {
        $q = User::where('role', 'resident');
        if ($bid = $this->effectiveBarangayId()) {
            $q->where('barangay_id', $bid);
        } elseif ($request->filled('barangay') && $request->barangay !== 'all') {
            $q->where('barangay_id', $request->barangay);
        }
        $residents = $q->with('barangay')->orderBy('name')->get();
        return view('admin.reports.residents', compact('residents'));
    }

    public function beneficiaries(Request $request)
    {
        $programId = $request->get('program');
        $program = $programId ? AssistanceProgram::findOrFail($programId) : null;
        $q = AssistanceBeneficiary::with(['resident.barangay', 'program']);
        if ($program) {
            $q->where('assistance_program_id', $program->id);
        }
        $items = $q->orderBy('reference_no')->get();
        $programs = AssistanceProgram::orderBy('title')->get();
        return view('admin.reports.beneficiaries', compact('items', 'programs', 'program'));
    }

    public function attendance(Request $request)
    {
        $eventId = $request->get('event');
        $event = $eventId ? Event::findOrFail($eventId) : null;
        $q = EventRegistration::with(['resident.barangay', 'event']);
        if ($event) {
            $q->where('event_id', $event->id);
        }
        $items = $q->orderBy('reference_no')->get();
        $events = Event::orderBy('title')->get();
        return view('admin.reports.attendance', compact('items', 'events', 'event'));
    }

    public function emergency(Request $request)
    {
        $q = EmergencyReport::with(['reporter', 'barangay']);
        if ($bid = $this->effectiveBarangayId()) {
            $q->where('barangay_id', $bid);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        $items = $q->orderByDesc('id')->get();
        return view('admin.reports.emergency', compact('items'));
    }
}