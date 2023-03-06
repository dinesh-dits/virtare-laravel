<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\ToolTipService;
use Illuminate\Http\Request;

class ToolTipController extends Controller
{
    //Tool Tip Listing
    public function tooltipListing(Request $request)
    {
        return (new ToolTipService)->tooltipListing($request);
    }

    // Form Listing
    public function formList(Request $request)
    {
        return (new ToolTipService)->formList($request);
    }

    //Add Tool Tip
    public function addToolTip(Request $request,$id)
    {
        return (new ToolTipService)->addToolTip($request,$id);
    }

    //Update Tool Tip
    public function updateToolTip(Request $request,$id)
    {
        return (new ToolTipService)->updateToolTip($request,$id);
    }
}
