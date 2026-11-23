<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('barangay_id')->orWhere('barangay_id', auth()->user()->barangay_id);
            })->orderBy('event_date')->paginate(15);
        return view('resident.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $registration = $event->registrations()->where('user_id', auth()->id())->first();
        return view('resident.events.show', compact('event', 'registration'));
    }

    public function register(Event $event)
    {
        $exists = EventRegistration::where('event_id', $event->id)->where('user_id', auth()->id())->exists();
        if ($exists) {
            return back()->withErrors(['event' => 'Already registered.']);
        }
        if ($event->registration_limit && $event->registrations()->count() >= $event->registration_limit) {
            return back()->withErrors(['event' => 'Registration full.']);
        }
        $ref = 'EV-'.str_pad((string) (EventRegistration::count() + 1), 5, '0', STR_PAD_LEFT);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'reference_no' => $ref,
            'qr_code' => 'EVT-'.$event->id.'-'.auth()->id().'-'.bin2hex(random_bytes(6)),
            'status' => 'registered',
        ]);
        Notification::push(auth()->id(), 'Event registration confirmed', 'You are registered for '.$event->title.'. Your QR code is ready.', 'success', 'event', route('resident.events.show', $event));
        return back()->with('status', 'Registered successfully.');
    }
}