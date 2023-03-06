<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\NonComplianceService;

class NonComplianceController extends Controller
{
    // List Non Compliance Patients
    public function nonCompliance(Request $request, $id = null)
    {
        return (new NonComplianceService)->nonComplianceList($request, $id);
    }
}
