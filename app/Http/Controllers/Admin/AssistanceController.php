<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\AssistanceBeneficiary;
use App\Models\AssistanceProgram;
use App\Models\Barangay;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    use ScopesAdminQueries;

    public function index(Request $request)
    {
        $q = AssistanceProgram::with('barangay');
        if ($bid = $this->effectiveBarangayId()) {
            $q->where(function ($x) use ($bid) {
                $x->where('barangay_id', $bid)->orWhereNull('barangay_id');
            });
        }
        $programs = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $stats = [
            'programs' => AssistanceProgram::count(),
            'beneficiaries' => AssistanceBeneficiary::count(),
            'claimed' => AssistanceBeneficiary::where('status', 'claimed')->count(),
            'pending' => AssistanceBeneficiary::where('status', 'qualified')->count(),
        ];
        return view('admin.assistance.index', compact('programs', 'stats'));
    }

    public function create()
    {
        $barangays = Barangay::ordered()->get();
        return view('admin.assistance.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:'.implode(',', array_keys(AssistanceProgram::types()))],
            'eligibility' => ['nullable', 'string'],
            'distribution_date' => ['nullable', 'date'],
            'distribution_time' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'beneficiary_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:'.implode(',', array_keys(AssistanceProgram::statuses()))],
        ]);
        $data['created_by'] = auth()->id();
        $program = AssistanceProgram::create($data);
        return redirect()->route('admin.assistance.show', $program)->with('status', 'Program created.');
    }

    public function show(AssistanceProgram $program)
    {
        $program->load(['barangay', 'beneficiaries.resident']);
        $residents = User::where('role', 'resident')->orderBy('name')->get();
        return view('admin.assistance.show', compact('program', 'residents'));
    }

    public function update(Request $request, AssistanceProgram $program)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:'.implode(',', array_keys(AssistanceProgram::types()))],
            'eligibility' => ['nullable', 'string'],
            'distribution_date' => ['nullable', 'date'],
            'distribution_time' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['nullable', 'exists:barangays,id'],
            'beneficiary_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:'.implode(',', array_keys(AssistanceProgram::statuses()))],
        ]);
        $program->update($data);
        return back()->with('status', 'Program updated.');
    }

    public function destroy(AssistanceProgram $program)
    {
        $program->delete();
        return redirect()->route('admin.assistance.index')->with('status', 'Program deleted.');
    }

    // Beneficiaries
    public function addBeneficiary(Request $request, AssistanceProgram $program)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);
        $exists = AssistanceBeneficiary::where('assistance_program_id', $program->id)
            ->where('user_id', $data['user_id'])->exists();
        if ($exists) {
            return back()->withErrors(['user_id' => 'Resident already a beneficiary.']);
        }
        $ref = 'AB-'.str_pad((string) (AssistanceBeneficiary::count() + 1), 5, '0', STR_PAD_LEFT);
        $ben = AssistanceBeneficiary::create([
            'assistance_program_id' => $program->id,
            'user_id' => $data['user_id'],
            'reference_no' => $ref,
            'status' => 'qualified',
            'qr_code' => 'AYUDA-'.$program->id.'-'.$data['user_id'].'-'.bin2hex(random_bytes(6)),
        ]);
        Notification::push($data['user_id'], 'Assistance QR code available', 'You are a beneficiary of '.$program->title.'. Your QR code is ready.', 'success', 'assistance', route('resident.assistance.show', $program));
        return back()->with('status', 'Beneficiary added and QR generated.');
    }

    public function removeBeneficiary(AssistanceBeneficiary $beneficiary)
    {
        $beneficiary->delete();
        return back()->with('status', 'Beneficiary removed.');
    }

    // Scan verify
    public function scanForm()
    {
        return view('admin.assistance.scan');
    }

    public function scanVerify(Request $request)
    {
        $code = $request->input('code');
        $ben = AssistanceBeneficiary::where('qr_code', $code)->with(['resident', 'program'])->first();
        if (!$ben) {
            return back()->withErrors(['code' => 'QR code not found.'])->withInput();
        }
        return view('admin.assistance.scan', compact('ben'));
    }

    public function scanClaim(Request $request, AssistanceBeneficiary $beneficiary)
    {
        if ($beneficiary->status === 'claimed') {
            return back()->withErrors(['code' => 'Already claimed.']);
        }
        $beneficiary->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'claimed_by' => auth()->id(),
        ]);
        Notification::push($beneficiary->user_id, 'Assistance claimed', 'Your '.$beneficiary->program->title.' has been claimed.', 'success', 'assistance');
        return redirect()->route('admin.assistance.scan')->with('status', 'Claim recorded.');
    }
}