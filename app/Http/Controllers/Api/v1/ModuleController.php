<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\ModuleService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    // Add Module
    public function createModule(Request $request)
    {
        return (new ModuleService)->addModule($request);
    }

    // List Module
    public function getModule(Request $request)
    {
        return (new ModuleService)->getModuleList($request);
    }
}
