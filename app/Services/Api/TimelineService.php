<?php

namespace App\Services\Api;

use App\Helper;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Transformers\Patient\NewPatientCountTransformer;

class TimelineService
{
    // Appointment Total Count
    public function appointmentTotal($request)
    {
        try {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            if ($fromDate < $toDate) {
                $seconds_diff = $toDate - $fromDate;
            } else {
                $seconds_diff = $fromDate - $toDate;
            }
            if ($seconds_diff / 3600 <= 24) {
                $data = DB::select(
                    "CALL getTotalAppointmentSummaryCount('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 8) {
                $data = DB::select(
                    "CALL dashboardWeekAppointment('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 32) {
                $data = DB::select(
                    "CALL dashboardMonthAppointment('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 366) {
                $data = DB::select(
                    "CALL dashboardYearAppointment('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            }
            return fractal()->collection($data)->transformWith(new NewPatientCountTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Patient Total Count
    public function patientTotal($request)
    {
        try {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            if ($fromDate < $toDate) {
                $seconds_diff = $toDate - $fromDate;
            } else {
                $seconds_diff = $fromDate - $toDate;
            }

            if ($seconds_diff / 3600 <= 24) {
                $data = DB::select(
                    "CALL getTotalPatientSummaryCount('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 8) {
                $data = DB::select(
                    "CALL dashboardWeekPatient('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 32) {
                $data = DB::select(
                    "CALL dashboardMonthPatient('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            } elseif ($seconds_diff / 3600 / 24 <= 366) {
                $data = DB::select(
                    "CALL dashboardYearPatient('" . $fromDateStr . "','" . $toDateStr . "')"
                );
            }
            return fractal()->collection($data)->transformWith(new NewPatientCountTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
