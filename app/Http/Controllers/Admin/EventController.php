<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\Barangay;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ScopesAdminQueries;

    public function index(Request $request)
    {
        $q = Event::with('barangay');
        if ($bid = $this->effectiveBarangayId()) {
            $q->where(function ($x) use ($bid) {
                $x->where('barangay_id', $bid)->orWhereNull('barangay_id');
            });
        }
        $events = $q->orderByDesc('event_date')->paginate(20)->withQueryString();
        $stats = [
            'events' => Event::count(),
            'registrations' => EventRegistration::count(),
            'attended' => EventRegistration::where('status', 'attended')->count(),
        ];
        return view('admin.events.index', compact('events', 'stats'));
    }

    public function create()
    {
        $barangays = Barangay::ordered()->get();
        return view('admin.events.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string', 'max:20'],
            'end_time' => ['nullable', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'registration_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ]);
        $data['created_by'] = auth()->id();
        $event = Event::create($data);
        return redirect()->route('admin.events.show', $event)->with('status', 'Event created.');
    }

    public function show(Event $event)
    {
        $event->load(['barangay', 'registrations.resident']);
        return view('admin.events.show', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string', 'max:20'],
            'end_time' => ['nullable', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'registration_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ]);
        $event->update($data);
        return back()->with('status', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('status', 'Event deleted.');
    }

    public function scanForm()
    {
        return view('admin.events.scan');
    }

    public function scanVerify(Request $request)
    {
        $code = $request->input('code');
        $reg = EventRegistration::where('qr_code', $code)->with(['resident', 'event'])->first();
        if (!$reg) {
            return back()->withErrors(['code' => 'QR code not found.'])->withInput();
        }
        return view('admin.events.scan', compact('reg'));
    }

    public function scanAttend(Request $request, EventRegistration $registration)
    {
        if ($registration->status === 'attended') {
            return back()->withErrors(['code' => 'Already attended.']);
        }
        $registration->update([
            'status' => 'attended',
            'attended_at' => now(),
            'attended_by' => auth()->id(),
        ]);
        Notification::send($registration->user_id, 'Event attendance recorded', 'Your attendance for '.$registration->event->title.' has been recorded.', 'success', 'event');
        return redirect()->route('admin.events.scan')->with('status', 'Attendance recorded.');
    }
}
