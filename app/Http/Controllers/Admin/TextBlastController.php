<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\Barangay;
use App\Models\TextBlast;
use App\Models\User;
use Illuminate\Http\Request;

class TextBlastController extends Controller
{
    use ScopesAdminQueries;

    public function index()
    {
        $blasts = TextBlast::with(['sender', 'barangay'])->orderByDesc('id')->paginate(20);
        return view('admin.textblast.index', compact('blasts'));
    }

    public function create()
    {
        $barangays = Barangay::ordered()->get();
        return view('admin.textblast.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required', 'in:citywide,barangay'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'recipient_group' => ['required', 'in:all,residents,officials'],
            'message' => ['required', 'string', 'max:1600'],
        ]);

        // count recipients
        $q = User::query();
        if ($data['scope'] === 'barangay' && !empty($data['barangay_id'])) {
            $q->where('barangay_id', $data['barangay_id']);
        }
        if ($data['recipient_group'] === 'residents') {
            $q->where('role', 'resident');
        } elseif ($data['recipient_group'] === 'officials') {
            $q->whereIn('role', User::OFFICIAL_ROLES);
        }
        $count = $q->count();

        $blast = TextBlast::create([
            'sender_id' => auth()->id(),
            'scope' => $data['scope'],
            'barangay_id' => $data['barangay_id'] ?? null,
            'recipient_group' => $data['recipient_group'],
            'message' => $data['message'],
            'recipient_count' => $count,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // In a real deployment, integrate an SMS gateway here.
        // For now we record the blast and create in-app notifications for recipients.
        $q->get()->each(function ($u) use ($data) {
            \App\Models\Notification::push($u->id, 'SMS advisory', $data['message'], 'info', 'sms');
        });

        return redirect()->route('admin.textblast.index')->with('status', 'Text blast sent to '.$count.' recipients (logged).');
    }
}