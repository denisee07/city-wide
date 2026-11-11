<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Support\CityBreakdown;
use Illuminate\Contracts\View\View;

/**
 * City-wide breakdown of every barangay, one section each.
 *
 * This page exists because the dashboard aggregates all 18 into a single set
 * of numbers - which hides the fact that most of the workload often sits in
 * one or two barangays. Each row drills into that barangay's boards via
 * ?barangay=<id>, and the whole console follows it through the filter.
 */
class BarangayController extends Controller
{
    use ScopesAdminQueries;

    public function index(): View
    {
        /* The per-barangay numbers live in CityBreakdown so the dashboard's
           pulse strip and this page can never disagree. */
        $rows = CityBreakdown::perBarangay();

        return view('admin.barangays.index', [
            'rows'       => $rows,
            'totals'     => CityBreakdown::totals($rows),
            'maxOpen'    => CityBreakdown::maxOpen($rows),
            'focusId'    => $this->effectiveBarangayId(),
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }
}
