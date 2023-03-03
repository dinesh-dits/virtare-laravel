<?php

namespace App\Listeners;

use App\Events\SetUpPasswordEvent;
use App\Helper;
use App\Models\User\User;

class SetUpPasswordListener
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
     * @param SetUpPasswordEvent $event
     */
    public function handle(SetUpPasswordEvent $event)
    {
        $user = User::where("email", $event->data['email'])->first();
        if (isset($user->id)) {
            $code = Helper::randomString(50);
            $forgetToken = ['forgetToken' => $code];
            User::where("email", $event->data['email'])->update($forgetToken);
            $base_url = env('APP_URL', null);
            if ($base_url == null) {
                $base_url = URL();
            }
            $forgotUrl = $base_url . "#/setup-password?code=" . $code;
            if ($event->data['email']) {
                $variable["name"] = $event->data['firstName'];
                $variable["url"] = $forgotUrl;
                $userDetail['email'] = $event->data['email'];
                $userDetail['name'] = $event->data['firstName'];
                Helper::sendMailTemplate($event->data['template_name'], json_encode($variable), $userDetail);
            }
        }
    }
}
