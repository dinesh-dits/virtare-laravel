<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Helper;
use App\Models\EmailStats;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\EmailStatsService;
class EmailStatsController extends Controller
{

      public function getStats()
    {
        /* $stats = EmailStats::with('user','escalation')
        ->whereHas('escalation', function($q) {           
            $q->where('entity_type', '=', 'Escalation');
        })->paginate(20);*/
        $perPage = env('PER_PAGE', 20);
        $stats = EmailStats::with('user', 'escalation')->paginate($perPage);
        if ($stats->count() > 0) {
            $response = array();
            foreach ($stats as $key => $stat) :
                $response[$key]['email'] = $stat->email;
                $response[$key]['status'] = $stat->status;
                $response[$key]['type'] = $stat->entity_type;
                $response[$key]['sent_on'] = $stat->sent_on;
                $response[$key]['updated_on'] = $stat->updated_at;
                $response[$key]['escaltion_udid'] = ($stat->entity_type == 'Escalation' && $stat->escalation && $stat->escalation->udid) ? $stat->escalation->udid : '';
            endforeach;            
            $meta = Helper::getPagination($stats, count($response), $perPage);

            //$response['meta']['pagination']['total'] = $employee['data']['total'];
            //$response['meta']['pagination']['count'] = $employee['total'];
            //$response['meta']['pagination']['per_page'] = $perPage;
            //$response['meta']['pagination']['current_page'] = $employee['data']['current_page'];
            //$response['meta']['pagination']['total_pages'] = $employee['data']['last_page'];
            //$response['meta']['pagination']['links']['next'] = ($employee['data']['next_page_url'])?$employee['data']['next_page_url']:'';       

            return response()->json(['data' => $response, 'meta' => $meta],  200);
        } else {
            return response()->json(['message' => 'No record found'],  200);
        }
    }
    public function update_stats(Request $request)
    {
        try {

            $data = $request->all();
            //$messageObj = json_encode($data);
            //Helper::commonMailjet('sanjeev.saini@ditstek.com', 'Virtare Health', $messageObj, 'RESPONSE');
            //$messageObj = json_encode($_POST);
            //Helper::commonMailjet('sanjeev.saini@ditstek.com', 'Virtare Health', $messageObj, 'RESPONSE');


            if ($request->MessageID && !empty($request->MessageID)) {
                $stat = EmailStats::where('message_id', $request->MessageID)->first();
                if (isset($stat->id) && $stat->id && $stat->status !=  $request->event) {
                    $stat->status =   $request->event;
                    $stat->save();
                }
            }
            return response()->json(['message' => 'Updated'],  200);
        } catch (Exception $e) {
            Helper::commonMailjet('sanjeev.saini@ditstek.com', 'Virtare Health', $e->getMessage(), 'RESPONSE');
            return response()->json(['message' => $e->getMessage()],  500);
        }
    }

    public function emailCount(Request $request)
    {
        return (new EmailStatsService)->emailCount($request);
    }

    public function emailGraph(Request $request)
    {
        return (new EmailStatsService)->emailGraph($request);
    }
}
