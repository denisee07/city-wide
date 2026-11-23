<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\AssistanceProgram;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    public function index()
    {
        $programs = AssistanceProgram::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('barangay_id')->orWhere('barangay_id', auth()->user()->barangay_id);
            })->orderByDesc('id')->paginate(15);
        return view('resident.assistance.index', compact('programs'));
    }

    public function show(AssistanceProgram $program)
    {
        $beneficiary = $program->beneficiaries()->where('user_id', auth()->id())->first();
        return view('resident.assistance.show', compact('program', 'beneficiary'));
    }
}