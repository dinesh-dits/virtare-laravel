<?php

namespace App\Transformers\Notification;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class NotificationTransformer extends TransformerAbstract
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


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        if ($this->showData) {
            $response = array([
                'id' => $data->id,
                "body" => $data->body,
                "title" => $data->title,
                "type" => @$data->entity,
                "type_id" => @$data->referenceId,
                "Isread" => $data->isRead,
                "time" => strtotime($data->createdAt),
            ]);
            return $response;
        } else {
            return [
                'id' => $data->id,
                "body" => $data->body,
                "title" => $data->title,
                "type" => @$data->entity,
                "type_id" => @$data->referenceId,
                "Isread" => $data->isRead,
                "time" => strtotime($data->createdAt),

                //    "created_user"=>fractal()->item($data->created_user)->transformWith(new UserTransformer(true))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),

            ];
        }
    }
}
