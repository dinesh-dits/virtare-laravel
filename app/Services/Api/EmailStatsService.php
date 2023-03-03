<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\EmailStats;
use Illuminate\Support\Facades\DB;

class EmailStatsService
{

    public function emailCount($request)
    {
        try {
            if ($request->fromDate && $request->toDate) {
                $fromDate = Helper::date($request->fromDate);
                $toDate = Helper::date($request->toDate);
            } else {
                $fromDate = '';
                $toDate = '';
            }
            $data = DB::select("CALL emailCount('" . $fromDate . "','" . $toDate . "')");
            $color = [
                'sent' => '#458FFE',
                'bounce' => '#E73149',
                'open' => '#f77029'
            ];
            foreach ($data as $key => $status) {
                $data[$key]->color = isset($color[$status->status]) ? $color[$status->status] : '#f77029';
            }
            $email = array();
            foreach ($data as $dataEmail) {
                array_push($email, $dataEmail->status);
            }
            $flagData = EmailStats::groupBy('email_stats.status')->get();
            $flagFinalCount = array();
            foreach ($flagData as $key => $value) {
                $flagArrayNew = new \stdClass();
                if (!in_array($value['status'], $email)) {
                    $flagArrayNew->total = 0;
                    $flagArrayNew->status = $value['status'];
                    $flagArrayNew->color = isset($color[$value['status']]) ? $color[$value['status']] : '#f77029';
                    array_push($flagFinalCount, $flagArrayNew);
                } else {
                    $key = array_search($value['status'], $email);
                    array_push($flagFinalCount, $data[$key]);
                }
            }
            return $flagFinalCount;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function emailGraph($request)
    {
        try {
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
