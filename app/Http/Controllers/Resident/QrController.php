<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;

class QrController extends Controller
{
    public function show()
    {
        return view('resident.qr.show');
    }
}