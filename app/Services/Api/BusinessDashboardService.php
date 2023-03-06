<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Library\ErrorLogGenerator;
use Illuminate\Support\Facades\DB;
use App\Models\Patient\PatientStaff;
use App\Models\Patient\PatientReferral;
use App\Transformers\Patient\PatientCountTransformer;
use App\Transformers\Dashboard\BillingPercentageTransformer;


class BusinessDashboardService
{

    // Patient Referals Count
    public function countReferal($request)
    {
        try {
            $data = array();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            if ($fromDate < $toDate) {
                $seconds_diff = $toDate - $fromDate;
            } else {
                $seconds_diff = $fromDate - $toDate;
            }
            if (auth()->user()->roleId == 3) {
                $staff = PatientStaff::selectRaw("group_concat(patientId) as patientId")->where('staffId', auth()->user()->staff->id)->groupBy("staffId")->first();
                if (!is_null($staff)) {
                    $type = explode(',', $staff->patientId);
                    $flag = PatientReferral::whereIn('patientId', $type)->get();
                }
                if (!empty($flag)) {

                    if ($seconds_diff / 3600 <= 24) {
                        $data = DB::select(
                            "CALL referalCount('" . $flag . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                        );
                    } elseif ($seconds_diff / 3600 / 24 <= 8) {
                        $data = DB::select(
                            "CALL referalWeekCount('" . $flag . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                        );
                    } elseif ($seconds_diff / 3600 / 24 <= 32) {
                        $data = DB::select(
                            "CALL referalMonthCount('" . $flag . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                        );
                    } elseif ($seconds_diff / 3600 / 24 <= 366) {
                        $data = DB::select(
                            "CALL referalYearCount('" . $flag . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                        );
                    }
                }
            } else {
                if ($seconds_diff / 3600 <= 24) {
                    $data = DB::select(
                        "CALL referalCount('" . '' . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                    );
                } elseif ($seconds_diff / 3600 / 24 <= 8) {
                    $data = DB::select(
                        "CALL referalWeekCount('" . '' . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                    );
                } elseif ($seconds_diff / 3600 / 24 <= 32) {
                    $data = DB::select(
                        "CALL referalMonthCount('" . '' . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                    );
                } elseif ($seconds_diff / 3600 / 24 <= 366) {
                    $data = DB::select(
                        "CALL referalYearCount('" . '' . "','" . $fromDateStr . "','" . $toDateStr . "','" . $provider . "','" . $providerLocation . "')"
                    );
                }
            }
            return $data;
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    //Call Status API's
    public function callStatus($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerId();
            $fromDate = Helper::date($request->input('fromDate'));
            $toDate = Helper::date($request->input('toDate'));
            $data = DB::select(
                "CALL communicationStatusCount('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //  CPT Code Billing Count
    public function billingSummary($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $data = DB::select(
                "CALL CPTCodeBillingSummary('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Finacial Stats Count
    public function financialStats($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $fromDate = Helper::date($request->input('fromDate'));
            $toDate = Helper::date($request->input('toDate'));
            $data = DB::select(
                "CALL billingPercentage('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            );
            return fractal()->item($data)->transformWith(new BillingPercentageTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
