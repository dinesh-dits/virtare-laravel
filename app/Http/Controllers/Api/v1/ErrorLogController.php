<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Library\ErrorLogGenerator;
use App\Http\Controllers\Controller;
use App\Services\Api\ErrorLogService;

class ErrorLogController extends Controller
{
  // List Errorlog
  public function listErrorLog(Request $request, $id = null)
  {
    return (new ErrorLogGenerator)->getErrorLog($id);
  }

  // Add Errorlog With DeviceInfo
  public function errorLogWithDeviceInfo(Request $request)
  {
    return (new ErrorLogService)->errorLogWithDeviceInfoAdd($request);
  }
}
