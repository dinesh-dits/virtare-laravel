<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ConditionService;
use App\Services\Api\CommunicationService;

class ConditionController extends Controller
{
    
    // List Condition
    public function listCondition(Request $request, $id=null)
    {
        return (new ConditionService)->conditionList($request, $id);
    }
   
}
