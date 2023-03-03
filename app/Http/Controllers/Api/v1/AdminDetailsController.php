<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\AdminDetailsService;

class AdminDetailsController extends Controller
{
    // Admin Details
    public function adminDetails(){
        return (new AdminDetailsService)->adminDetails();
    }
}
