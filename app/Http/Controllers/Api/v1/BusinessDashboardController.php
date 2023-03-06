<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\BusinessDashboardService;


class BusinessDashboardController extends Controller
{

    // Referal Count
    public function referalCount(Request $request)
    {
      return (new BusinessDashboardService)->countReferal($request);
    }

    // Call Status Count
    public function callStatus(request $request)
    {
        return (new BusinessDashboardService)->callStatus($request);
    }

    // CPT Code Billing Count
    public function billingSummary(Request $request)
    {
        return (new BusinessDashboardService)->billingSummary($request);
    }

    // Finacial Stats Count
    public function financialStats(Request $request){
        return (new BusinessDashboardService)->financialStats($request);
    }

    
    
}
