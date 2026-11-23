<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Models\Barangay;
use App\Models\CensusRecord;
use App\Models\User;
use Illuminate\Http\Request;

class CensusController extends Controller
{
    use ScopesAdminQueries;

    public function index(Request $request)
    {
        $q = CensusRecord::with(['barangay', 'user']);
        if ($bid = $this->effectiveBarangayId()) {
            $q->where('barangay_id', $bid);
        } elseif ($request->filled('barangay') && $request->barangay !== 'all') {
            $q->where('barangay_id', $request->barangay);
        }
        if ($request->filled('sex')) {
            $q->where('sex', $request->sex);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($x) use ($s) {
                $x->where('full_name', 'like', "%{$s}%")
                    ->orWhere('household_no', 'like', "%{$s}%")
                    ->orWhere('purok', 'like', "%{$s}%");
            });
        }
        $records = $q->orderByDesc('id')->paginate(25)->withQueryString();

        $barangays = Barangay::ordered()->get();
        $stats = [
            'total' => (clone $q)->count(),
            'male' => (clone $q)->where('sex', 'male')->count(),
            'female' => (clone $q)->where('sex', 'female')->count(),
            'households' => (clone $q)->distinct('household_no')->count('household_no'),
        ];

        // per-barangay summary for citywide view
        $byBarangay = Barangay::ordered()->get()->map(function ($b) {
            $base = CensusRecord::where('barangay_id', $b->id);
            return [
                'barangay' => $b,
                'total' => $base->count(),
                'male' => (clone $base)->where('sex', 'male')->count(),
                'female' => (clone $base)->where('sex', 'female')->count(),
                'households' => (clone $base)->distinct('household_no')->count('household_no'),
            ];
        });

        return view('admin.census.index', compact('records', 'barangays', 'stats', 'byBarangay'));
    }

    public function create()
    {
        $barangays = Barangay::ordered()->get();
        $residents = User::where('role', 'resident')->orderBy('name')->get();
        return view('admin.census.create', compact('barangays', 'residents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barangay_id' => ['required', 'exists:barangays,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'household_no' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'household_size' => ['required', 'integer', 'min:1', 'max:50'],
            'purok' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'income_bracket' => ['nullable', 'string', 'max:50'],
            'is_head' => ['nullable', 'boolean'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'is_pwd' => ['nullable', 'boolean'],
            'is_senior' => ['nullable', 'boolean'],
        ]);
        $data['is_head'] = $request->boolean('is_head');
        $data['is_pwd'] = $request->boolean('is_pwd');
        $data['is_senior'] = $request->boolean('is_senior');
        if (empty($data['age']) && !empty($data['birth_date'])) {
            $data['age'] = \Carbon\Carbon::parse($data['birth_date'])->age;
        }
        CensusRecord::create($data);
        return redirect()->route('admin.census.index')->with('status', 'Census record added.');
    }

    public function edit(CensusRecord $census)
    {
        $barangays = Barangay::ordered()->get();
        $residents = User::where('role', 'resident')->orderBy('name')->get();
        return view('admin.census.edit', compact('census', 'barangays', 'residents'));
    }

    public function update(Request $request, CensusRecord $census)
    {
        $data = $request->validate([
            'barangay_id' => ['required', 'exists:barangays,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'household_no' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'household_size' => ['required', 'integer', 'min:1', 'max:50'],
            'purok' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'income_bracket' => ['nullable', 'string', 'max:50'],
            'is_head' => ['nullable', 'boolean'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'is_pwd' => ['nullable', 'boolean'],
            'is_senior' => ['nullable', 'boolean'],
        ]);
        $data['is_head'] = $request->boolean('is_head');
        $data['is_pwd'] = $request->boolean('is_pwd');
        $data['is_senior'] = $request->boolean('is_senior');
        if (empty($data['age']) && !empty($data['birth_date'])) {
            $data['age'] = \Carbon\Carbon::parse($data['birth_date'])->age;
        }
        $census->update($data);
        return redirect()->route('admin.census.index')->with('status', 'Census record updated.');
    }

    public function destroy(CensusRecord $census)
    {
        $census->delete();
        return redirect()->route('admin.census.index')->with('status', 'Census record deleted.');
    }
}