<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\Barangay;
use App\Models\EmergencyAdvisory;
use App\Models\EmergencyReport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    use ScopesAdminQueries;

    public function index(Request $request)
    {
        $q = EmergencyReport::with(['reporter', 'barangay', 'assignee']);
        if ($bid = $this->effectiveBarangayId()) {
            $q->where('barangay_id', $bid);
        } elseif ($request->filled('barangay') && $request->barangay !== 'all') {
            $q->where('barangay_id', $request->barangay);
        }
        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        $reports = $q->orderByDesc('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => EmergencyReport::count(),
            'active' => EmergencyReport::whereIn('status', ['submitted', 'received', 'under_review', 'responding'])->count(),
            'critical' => EmergencyReport::where('priority', 'critical')->whereIn('status', ['submitted', 'received', 'under_review', 'responding'])->count(),
            'resolved' => EmergencyReport::where('status', 'resolved')->count(),
        ];

        $barangays = Barangay::ordered()->get();
        $advisories = EmergencyAdvisory::orderByDesc('id')->limit(10)->get();

        return view('admin.emergency.index', compact('reports', 'stats', 'barangays', 'advisories'));
    }

    public function show(EmergencyReport $emergencyReport)
    {
        $report = $emergencyReport->load(['reporter', 'barangay', 'assignee']);
        $officials = User::whereIn('role', User::OFFICIAL_ROLES)->orderBy('name')->get();
        return view('admin.emergency.show', compact('report', 'officials'));
    }

    public function update(Request $request, EmergencyReport $emergencyReport)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(EmergencyReport::statuses()))],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'response_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (!empty($data['status']) && $data['status'] === 'responding' && !$emergencyReport->acknowledged_at) {
            $data['acknowledged_at'] = now();
        }
        if ($data['status'] === 'resolved') {
            $data['resolved_at'] = now();
        }
        $emergencyReport->update($data);

        Notification::push($emergencyReport->user_id, 'Emergency report updated', 'Your report '.$emergencyReport->reference_no.' is now '.$emergencyReport->statusLabel().'.', 'info', 'emergency', route('resident.emergency.show', $emergencyReport));

        return redirect()->route('admin.emergency.show', $emergencyReport)->with('status', 'Report updated.');
    }

    // Advisories
    public function advisories()
    {
        $advisories = EmergencyAdvisory::with(['barangay', 'publisher'])->orderByDesc('id')->paginate(20);
        $barangays = Barangay::ordered()->get();
        return view('admin.emergency.advisories', compact('advisories', 'barangays'));
    }

    public function storeAdvisory(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'in:'.implode(',', array_keys(EmergencyAdvisory::categories()))],
            'scope' => ['required', 'in:citywide,barangay'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'is_critical' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);
        $data['is_critical'] = $request->boolean('is_critical');
        $data['published_by'] = auth()->id();
        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        EmergencyAdvisory::create($data);

        // notify residents
        $q = User::where('role', 'resident');
        if ($data['scope'] === 'barangay' && !empty($data['barangay_id'])) {
            $q->where('barangay_id', $data['barangay_id']);
        }
        $title = ($data['is_critical'] ? 'CRITICAL: ' : '').$data['title'];
        $q->get()->each(function ($u) use ($title, $data) {
            Notification::push($u->id, $title, $data['body'], $data['is_critical'] ? 'danger' : 'info', 'emergency');
        });

        return redirect()->route('admin.emergency.advisories')->with('status', 'Advisory published.');
    }

    public function destroyAdvisory(EmergencyAdvisory $advisory)
    {
        $advisory->delete();
        return redirect()->route('admin.emergency.advisories')->with('status', 'Advisory deleted.');
    }
}