<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\FlagService;
use App\Http\Controllers\Controller;

class FlagController extends Controller
{
    // List Flag
    public function listFlag(Request $request)
    {
        return (new FlagService)->flagList($request);
    }
}
