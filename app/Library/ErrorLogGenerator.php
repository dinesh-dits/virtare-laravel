<?php

namespace App\Library;

use Illuminate\Support\Str;
use App\Models\ErrorLog\ErrorLog;
use App\Transformers\ErrorLog\ErrorLogTransformer;
use Illuminate\Support\Facades\URL;

class ErrorLogGenerator
{
    public static function createLog($request, $exception, $user_id = "")
    {
        try {
            $udid = Str::uuid()->toString();
            
            $base = URL::to('/');            
            if (!empty($request)) {
                $requestParameter = json_encode($request->all());
                $request_url = $base . $request->server("REQUEST_URI");
                $request_method = $request->server("REQUEST_METHOD");
            } else {
                $request_url = "";
                $request_method = "";
                $requestParameter = "";
            }

            // die;
            $data = [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ];

            $dataArr = [
                'udid' => $udid,
                'requestUrl' => $request_url,
                'requestParameter' => $requestParameter,
                'requestMethod' => $request_method,
                'file' => $exception->getFile(),
                'errorSummary' => 'Line ' . $data['line'] . ' ' . $data['message']
            ];

            if (!empty($user_id)) {
                $dataArr["userId"] = $user_id;
            }

            $lastId = ErrorLog::insertGetId($dataArr);
            if (!empty($lastId)) {
                $ErrorLogData = ErrorLog::where('id', $lastId)->first();
                if (!empty($ErrorLogData)) {
                    return $ErrorLogData->udid;
                }
            }
        } catch (\Exception $e) {
            // throw new \RuntimeException($e);
            echo $e->getMessage().''.$e->getFile().''.$e->getLine();
        }
    }

    public static function getErrorLog($udid = "")
    {
        if (!empty($udid)) {
            $ErrorLogData = ErrorLog::where('udid', $udid)->first();
            if (!empty($ErrorLogData)) {
                $resp = fractal()->item($ErrorLogData)->transformWith(new ErrorLogTransformer())->toArray();
                return $resp;
            } else {
                return $message = ['message' => "Invalid Log id."];
            }
        } else {
            return $message = ['message' => "Invalid Log id."];
        }
    }
}
