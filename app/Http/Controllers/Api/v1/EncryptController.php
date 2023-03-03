<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\EncryptService;

class EncryptController extends Controller
{
    public function encryptParameter(Request $request)
    {
        return (new EncryptService)->encryptParameter($request);
    }
}