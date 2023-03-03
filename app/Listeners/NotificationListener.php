<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Workflow\workFlowQueue;
use Carbon;
use App\Models\User\UserDeviceToken;
use App\Services\Api\PushNotificationService;
class NotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NotificationEvent  $event
     * @return void
     */
    public function handle(NotificationEvent $event)
    {
        $pushnotification = new PushNotificationService();
        $data = array(
            'id' => $event->notification->id,
            "title" => $event->notification->title,
            "body" => $event->notification->body,
            "type" => $event->notification->entity,
            "typeId" => $event->notification->referenceId,
        );
        $userId[] = $event->notification->userId;
//        print_r( $userId);
//        print_r($data);
        $pushnotification->sendNotification($userId, $data);

    }
}
