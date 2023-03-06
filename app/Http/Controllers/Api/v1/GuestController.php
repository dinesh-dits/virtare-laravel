<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\GuestService;
use App\Http\Controllers\Controller;

class GuestController extends Controller
{

    // Add Guest
    public function guest(Request $request)
    {
        return (new GuestService)->addGuest($request);        
    }
}
