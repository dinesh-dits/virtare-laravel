<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\TermsConditionsService;

class TermsConditionsController extends Controller
{
    // Terms and Condition
    public function __invoke(Request $request)
    {
        return (new TermsConditionsService())->termsConditions($request);
    }
}
