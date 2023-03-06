<?php

namespace App\Services\Api;

use Exception;
use Illuminate\Support\Str;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\Auth;
use App\Models\ExportReportRequest\ExportReportRequest;
use App\Models\ExportReportRequest\VitalPdfReportExportRequest;
use App\Transformers\ExportReportRequest\ExportReportRequestTransformer;

class ExportReportRequestService
{
    // Insert Export Request
    public static function insertExportRequest($request)
    {
        try {

            if ($request->input('reportType')) {
                $reportType = $request->input('reportType');
            } else {
                return response()->json(['message' => "reportType is required."], 500);
            }

            if ($request->input('timezone')) {
                $timezone = $request->input('timezone');
            } else {
                $timezone = "";
            }

            $userId = Auth::id();
            $udid = Str::uuid()->toString();

            $input = [
                'deletedBy' => 1,
                'isActive' => 0,
                'isDelete' => 1
            ];

            ExportReportRequest::where("userId", $userId)->update($input);

            $insertArr = [
                "reportType" => $reportType,
                "userId" => $userId,
                "udid" => $udid,
                "isActive" => 1
            ];

            if (!empty($timezone)) {
                $insertArr["customTimezone"] = $timezone;
            }

            $lastid = ExportReportRequest::insertGetId($insertArr);

            if ($lastid) {
                $serviceData = ExportReportRequest::where('id', $lastid)->first();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $resp = fractal()->item($serviceData)->transformWith(new ExportReportRequestTransformer())->toArray();
                $endData = array_merge($message, $resp);
                return $endData;
                // return response()->json(['message' => trans('messages.createdSuccesfully')],  200);
            }
            return response()->json(['message' => "server internal error."], 500);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Insert PDF Export Request
    public static function insertPdfExportRequest($request)
    {
        try {
            $reportType = "";
            if ($request->input('reportType')) {
                $reportType = $request->input('reportType');
            } else {
                return response()->json(['message' => "reportType is required."], 400);
            }

            if ($request->input('fieldId')) {
                $fieldId = $request->input('fieldId');
            } else {
                return response()->json(['message' => "fieldId is required."], 400);
            }

            if ($request->input('fromDate')) {
                $fromDate = $request->input('fromDate');
            } else {
                $fromDate = "";
            }

            if ($request->input('toDate')) {
                $toDate = $request->input('toDate');
            } else {
                $toDate = "";
            }

            if ($request->input('timezone')) {
                $timezone = $request->input('timezone');
            } else {
                $timezone = "";
            }

            if ($request->input('deviceTypeId')) {
                $deviceTypeId = $request->input('deviceTypeId');
            } else {
                $deviceTypeId = "";
            }

            if ($request->input('patientId')) {

                $patientUdid = $request->input('patientId');
                $patient = Patient::Where("udid", $patientUdid)->first();

                if (!empty($patient)) {
                    $patientId = $patient->id;
                } else {
                    return response()->json(['message' => "patient id required."], 400);
                }
            } else {

                if (isset(auth()->user()->patient)) {
                    $patientId = auth()->user()->patient->id;
                } else {
                    return response()->json(['message' => "patient id required."], 400);
                }
            }

            $requestTypeArr = array('vital_pdf');
            if (in_array($reportType, $requestTypeArr, true)) {
                $base_url = env('PDF_APP_URL', null);
                $strObj = explode("_", $reportType);
                if (isset($strObj[0])) {
                    $pdfType = $strObj[0];
                }

                if ($base_url == null) {
                    $base_url = URL() . "/" . $pdfType . "/pdf/report";
                } else {
                    $base_url = $base_url . $pdfType . "/pdf/report";
                }

                $userId = Auth::id();
                $udid = Str::uuid()->toString();

                $input = [
                    'deletedBy' => 1,
                    'isActive' => 0,
                    'isDelete' => 1
                ];

                VitalPdfReportExportRequest::where("userId", $userId)->update($input);

                $insertArr = [
                    "userId" => $userId,
                    "udid" => $udid,
                    "fieldId" => $fieldId,
                    "patientId" => $patientId,
                    "fromDate" => $fromDate,
                    "toDate" => $toDate,
                    "isActive" => 1
                ];

                if (!empty($timezone)) {
                    $insertArr["customTimezone"] = $timezone;
                }

                if (!empty($deviceTypeId)) {
                    $insertArr["deviceTypeId"] = $deviceTypeId;
                }

                $lastid = VitalPdfReportExportRequest::insertGetId($insertArr);

                if ($lastid) {
                    $serviceData = VitalPdfReportExportRequest::where('id', $lastid)->first();
                    $udid = $serviceData->udid;
                    $obj = array(
                        "pdf_url" => $base_url . "/" . $udid
                    );
                }

                $message = ['message' => trans('messages.createdSuccesfully'), 'reportType' => $reportType];
                $endData = array_merge($message, $obj);
                return response()->json($endData, 200);
            }
            return response()->json(['message' => "Invalid Pdf Type."], 400);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Report Request
    public static function checkReportRequest($id = "", $reportType = "")
    {
        try {
            if (!empty($id) && !empty($reportType)) {
                $resultData = ExportReportRequest::where('reportType', $reportType)->where('udid', $id)->where('isActive', "1")->first();
                if (!empty($resultData)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // PDF Request
    public static function checkPdfRequest($id = "")
    {
        try {
            if (!empty($id)) {
                $resultData = VitalPdfReportExportRequest::where('udid', $id)->where('isActive', "1")->first();
                if (!empty($resultData)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
