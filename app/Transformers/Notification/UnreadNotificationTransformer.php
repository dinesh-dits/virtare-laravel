<?php

namespace App\Transformers\Notification;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class UnreadNotificationTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        return [
               "body"=>$data->body,
               "title"=>$data->title,
               "type"=>$data->notificationType ? $data->notificationType->name : '',
               "Isread"=>$data->isRead,
               "time"=>date('H:i a',strtotime($data->createdAt)),
               "entity"=>$data->entity,
        ];
    }
}
