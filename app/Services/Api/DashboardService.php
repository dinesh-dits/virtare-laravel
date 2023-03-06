<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Dashboard\Timezone;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigMessage\MessageLog;
use App\Transformers\Dashboard\TimezoneTransformer;
use App\Transformers\Patient\PatientCountTransformer;
use App\Transformers\MessageLog\MessageLogTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class DashboardService
{

    // Staff Network Count
    public function staffNetwork($request)
    {
        try {
            $fromDate = Helper::date($request->input('fromDate'));
            $toDate = Helper::date($request->input('toDate'));
            $data = DB::select(
                "CALL getStaffNeworkCount('" . $fromDate . "','" . $toDate . "')",
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Specialization Count
    public function staffSpecialization($request)
    {
        try {
            $fromDate = Helper::date($request->input('fromDate'));
            $toDate = Helper::date($request->input('toDate'));
            $data = DB::select(
                "CALL getStaffSpecializationCount('" . $fromDate . "','" . $toDate . "')",
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Specialization New Count
    public function staffSpecializationNew($request)
    {
        try {
            $timelineId = $request->timelineId;
            $startEndDate = DB::select('CALL getGlobalStartEndDate(' . $timelineId . ')');
            if ($startEndDate[0]) {
                $startEndDate = $startEndDate[0];
                if ($startEndDate->conditions == "-") {
                    $timelineStartDate = strtotime($startEndDate->endDate);
                    $timelineEndDate = strtotime($startEndDate->startDate);
                } else {
                    echo "+++++++";
                    $timelineStartDate = strtotime($startEndDate->startDate);
                    $timelineEndDate = strtotime($startEndDate->endDate);
                }
            }
            $data = DB::select(
                'CALL getStaffSpecializationCountNew(' . $timelineStartDate . ',' . $timelineEndDate . ')',
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Sent Message
    public function sendMessage($request)
    {
        try {
            $param = $request->all();
            if (isset($param["sendTo"]) && !empty($param["sendTo"]) && isset($param["sendText"]) && !empty($param["sendText"])) {
                $sendTo = $param["sendTo"];
                $sendText = $param["sendText"];
                $response = Helper::sendBandwidthMessage($sendText, $sendTo);
                if (isset($response->id)) {
                    return response()->json(['data' => $response, 'messageId' => $response->id, 'message' => "message sent successfully"], 200);
                } else {
                    return response()->json(['message' => "something Wrong.", $response], 500);
                }
            } else {
                return response()->json(['message' => "sendTO and sendText Fields are Required."], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Get Timezone
    public function getTimezone($request)
    {
        try {
            $data = Timezone::whereNull('deletedAt')->where('isActive', 1);
            if (isset($request->search) && !empty($request->search)) {
                $data->Where("timezone.timeZone", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("timezone.countryCode", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("timezone.UTCOffset", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("timezone.UTCDSTOffset", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("timezone.abbr", 'LIKE', "%" . $request->search . "%");
            }
            /*if (isset($request->orderField) && $request->orderField == 'countryCode') {
                $data->orderBy('timezone.countryCode', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'abbr') {
                $data->orderBy('timezone.timeZone', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'timeZone') {
                $data->orderBy('timezone.timeZone', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'UTCOffset') {
                $data->orderBy('timezone.UTCOffset', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'UTCDSTOffset') {
                $data->orderBy('timezone.UTCDSTOffset', $request->orderBy);
            } else {
                $data->orderBy('timezone.timeZone', 'ASC');
            }*/
          //  $data->groupBy('timezone.UTCOffset');
            $data->orderBy('timezone.id', 'asc');
            $data = $data->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new TimezoneTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Get Timezone
    public function getEmailLogs($request, $id)
    {
        try {
            $data = MessageLog::where('isActive', 1);

            if (isset($request->search) && !empty($request->search)) {
                $data->Where("from", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("to", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("subject", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("status", 'LIKE', "%" . $request->search . "%");
                $data->orWhere("type", 'LIKE', "%" . $request->search . "%");
            }

            if (isset($request->orderField) && $request->orderField == 'from') {
                $data->orderBy('from', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'to') {
                $data->orderBy('to', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'subject') {
                $data->orderBy('subject', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'status') {
                $data->orderBy('status', $request->orderBy);
            } elseif (isset($request->orderField) && $request->orderField == 'type') {
                $data->orderBy('type', $request->orderBy);
            } else {
                $data->orderBy('createdAt', 'DESC');
            }
            $data = $data->paginate(env('PER_PAGE', 20));
            ///MessageLogTransformer
            return fractal()->collection($data)->transformWith(new MessageLogTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
