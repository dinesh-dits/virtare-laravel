<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\PushNotificationService;
use App\Http\Requests\Notification\NotificationSendRequest;

class PushNotificationController extends Controller
{
    // Notification Show
    public function notificationShow(Request $request)
    {
        return (new PushNotificationService)->showNotification($request);
    }

    // Show UnRead Notification
    public function showUnreadNotification(Request $request)
    {
        return (new PushNotificationService)->showUnreadNotification($request);
    }

    // Sent Notification
    public function send(NotificationSendRequest $request){
        
        $data = array(
                        "title" => $request->title,
                        "body" =>$request->message,
                        "type" =>$request->type,
        );
        $notification = (new PushNotificationService)->sendNotification($request->user,$data);
        return redirect()->to('notification')->with('message', 'Send Successfully');
    }
}
