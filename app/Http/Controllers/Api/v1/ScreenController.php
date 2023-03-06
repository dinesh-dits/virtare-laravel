<?php

namespace App\Http\Controllers\Api\v1;

use App\Services\Api\ScreenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    // Add Screen
    public function createScreen(Request $request)
    {
        return (new ScreenService)->addScreen($request);
    }

    // List Screen
    public function getScreen(Request $request)
    {
        return (new ScreenService)->getScreenList($request);
    }
}
