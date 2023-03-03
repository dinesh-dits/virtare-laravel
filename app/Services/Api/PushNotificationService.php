<?php

namespace App\Services\Api;

use App\FCM;
use Exception;
use App\Helper;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserDeviceToken;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification\Notification;
use App\Transformers\Notification\NotificationListTransformer;
use App\Transformers\Notification\UnreadNotificationTransformer;

class PushNotificationService
{
    // Sent Notification
    public function sendNotification(array $users, $data)
    {
        try {
            $deviceToken = array();
            $currentUser = Auth::id();
            if (empty($currentUser)) {
                $currentUser = 1;
            }
            foreach ($users as $userId) {
                /*$user = User::find($userId);
                if(!empty($user->deviceToken)){
                    array_push($deviceToken, $user->deviceToken);
                }*/
                $userData = UserDeviceToken::where('userId', $userId)->get();
                foreach ($userData as $user) {
                    if (!empty($user->deviceToken)) {
                        $deviceToken[] = $user->deviceToken;
                    }
                }
            }
            if (
                !empty($deviceToken)) {

                $fcm = new FCM();
                $fcm->deviceId($deviceToken);
                $fcm->notifications([
                    "title" => $data['title'],
                    "body" => $data['body']
                ]);
                $fcm->data([
                    "type" => $data['type'],
                    "typeId" => $data['typeId']
                ]);
                $data = $fcm->send();
//                print_r($data);
                return response()->json(['message' => trans('messages.notification')], 200);
            }
            return response()->json(['message' => trans('messages.notification_failed')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Show Notification
    public function showNotification($request)
    {
        try {
            // $notification = Notification::where('userId', Auth::id())->orderBy("id", "DESC")->get();
            $notificationData = Notification::select(DB::raw('*,DATE(createdAt) as year'))->where('userId', Auth::id())->orderBy("id", "DESC")->paginate(env('PER_PAGE', 10));
            $notificationUnread = Notification::where('userId', Auth::id())->where('isRead', '0')->count();
            if ($notificationUnread > 0 && empty($request->count)) {
                $isRead = ['isRead' => '1'];
                Notification::where('userId', Auth::id())->update($isRead);
                $notificationDatas = Notification::where('userId', Auth::id())->first();
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notificationDatas->id,
                    'value' => json_encode($isRead), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $notification = Helper::dateGroup($notificationData, 'createdAt');

            $dataNotification = array();
            foreach ($notification as $value) {
                $dataInfo = array();
                foreach ($value['data'] as $data) {
                    $dataInfoData =
                        [
                            'id' => $data->id,
                            "body" => $data->body,
                            "title" => $data->title,
                            "type" => @$data->entity,
                            "type_id" => @$data->referenceId,
                            "Isread" => $data->isRead,
                            "time" => strtotime($data->createdAt),
                        ];
                    array_push($dataInfo, $dataInfoData);
                }
                $notificationInputData = [
                    'date' => $value['year'], 'value' => $dataInfo
                ];

                array_push($dataNotification, $notificationInputData);
            }
            // $notificationList = fractal()->collection($notificationData)->transformWith(new NotificationListTransformer)->toArray();
            if (empty($request->count)) {
                $meta = Helper::getPagination($notificationData, $notificationUnread, env('PER_PAGE', 10));
                // $new = array_merge($notification, ['count' => $notificationUnread]);
                return response()->json(['data' => $dataNotification, 'meta' => $meta], 200);
            } else {
                return ['count' => $notificationUnread];
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Show Unread Notification
    public function showUnreadNotification($request)
    {
        try {
            $notification = Notification::where([['userId', Auth::id()], ['isRead', '0']])->orderBy("id", "DESC")->get();
            return fractal()->collection($notification)->transformWith(new UnreadNotificationTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // IOS Token
    public function ios_token($deviceToken)
    {
        try {
            $serverKey = env("FCM_SERVER_KEY");
            $headers = [
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ];
            $fields = [
                'application' => "com.tethr.virtarehealth",
                'sandbox' => env("FCM_SANDBOX"),
                'apns_tokens' => array($deviceToken)
            ];
            $client = new Client();
            $request = $client->post("https://iid.googleapis.com/iid/v1:batchImport", [
                'headers' => $headers,
                "body" => json_encode($fields),
            ]);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response['results'][0]['registration_token'];
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
