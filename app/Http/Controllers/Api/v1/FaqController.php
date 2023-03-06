<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\FaqService;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
     /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    // List FAQ
    public function __invoke(Request $request)
    {
        return (new FaqService)->list($request);
    }
}
