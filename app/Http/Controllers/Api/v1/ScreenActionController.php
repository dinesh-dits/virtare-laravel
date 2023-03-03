<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\ScreenActionService;
use Illuminate\Http\Request;

class ScreenActionController extends Controller
{
    // Add Screen Action
    public function creatScreenAction(Request $request)
    {
        return (new ScreenActionService)->addScreenAction($request);
    }

    // Add Patient Screen Action
    public function createPatientScreenAction(Request $request,$id)
    {
        return (new ScreenActionService)->addPatientScreenAction($request,$id);
    }
}
