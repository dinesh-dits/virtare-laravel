<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\TagService;
use App\Http\Controllers\Controller;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class TagController extends Controller
{
    public function listTag(request $request,$id="")
    {
        return (new TagService)->listTag($request,$id);
    }
}