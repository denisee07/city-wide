<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MapController extends Controller
{
    use ScopesAdminQueries;

    public function index(): View
    {
        $complaints = $this->scoped(Complaint::query());

        $statusCounts = (clone $complaints)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (clone $complaints)->count();
        $plotted = (clone $complaints)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();

        /* The map opens on whichever barangay the console is showing, and on
           the whole city when it is showing all 18. */
        $barangay = ($id = $this->effectiveBarangayId()) === null
            ? null
            : Barangay::query()->find($id);

        [$latitude, $longitude] = $barangay
            ? $barangay->coordinate()
            : Barangay::CITY_CENTER;

        return view('admin.map.index', [
            'statusCounts' => $statusCounts,
            'total' => $total,
            'plotted' => $plotted,
            'unplotted' => $total - $plotted,
            'badges' => ComplaintController::STATUS_BADGES,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $barangay ? Barangay::BARANGAY_ZOOM : Barangay::CITY_ZOOM,
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }
}
