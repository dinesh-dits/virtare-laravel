<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\ServiceNameService;
use Illuminate\Http\Request;

class ServiceNameController extends Controller
{

    // List Service
    public function listService(Request $request, $id = NULL)
    {
        return (new ServiceNameService)->listService($request, $id);
    }

    // Add Service
    public function createService(Request $request)
    {
        return (new ServiceNameService)->createService($request);
    }

    // Update Service
    public function updateService(Request $request, $id)
    {
        return (new ServiceNameService)->updateService($request, $id);
    }

    // Delete Service
    public function deleteService(Request $request, $id)
    {
        return (new ServiceNameService)->deleteService($request, $id);
    }
}
