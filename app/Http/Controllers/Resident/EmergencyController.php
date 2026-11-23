<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\EmergencyReport;
use App\Models\Notification;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function index()
    {
        $reports = EmergencyReport::where('user_id', auth()->id())
            ->orderByDesc('id')->paginate(15);
        return view('resident.emergency.index', compact('reports'));
    }

    public function create()
    {
        $barangays = Barangay::ordered()->get();
        $user = auth()->user();
        return view('resident.emergency.create', compact('barangays', 'user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(EmergencyReport::categories()))],
            'description' => ['required', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:500'],
            'barangay_id' => ['required', 'exists:barangays,id'],
            'destination' => ['required', 'in:barangay,city'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['user_id'] = auth()->id();
        $data['priority'] = in_array($data['category'], ['fire', 'medical', 'earthquake']) ? 'critical' : 'medium';
        $data['reference_no'] = 'EMG-'.now()->format('Ymd').'-'.str_pad((string) (EmergencyReport::count() + 1), 4, '0', STR_PAD_LEFT);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('emergency', 'public');
        }

        $report = EmergencyReport::create($data);

        // notify officials of destination
        if ($data['destination'] === 'city') {
            \App\Models\User::whereIn('role', \App\Models\User::OFFICIAL_ROLES)->get()->each(function ($u) use ($report) {
                Notification::send($u->id, 'New emergency report', 'Report '.$report->reference_no.' ('.$report->categoryName().') needs attention.', 'danger', 'emergency', route('admin.emergency.show', $report));
            });
        } else {
            \App\Models\User::whereIn('role', \App\Models\User::OFFICIAL_ROLES)->where('barangay_id', $report->barangay_id)->get()->each(function ($u) use ($report) {
                Notification::send($u->id, 'New emergency report', 'Report '.$report->reference_no.' ('.$report->categoryName().') in your barangay.', 'danger', 'emergency', route('admin.emergency.show', $report));
            });
        }

        Notification::send(auth()->id(), 'Emergency report submitted', 'Your report '.$report->reference_no.' has been submitted.', 'success', 'emergency', route('resident.emergency.show', $report));

        return redirect()->route('resident.emergency.show', $report)->with('status', 'Emergency report submitted.');
    }

    public function show(EmergencyReport $emergencyReport)
    {
        abort_unless($emergencyReport->user_id === auth()->id(), 403);
        return view('resident.emergency.show', ['report' => $emergencyReport]);
    }
}
