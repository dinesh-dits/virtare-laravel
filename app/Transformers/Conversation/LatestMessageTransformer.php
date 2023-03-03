<?php

namespace App\Transformers\Conversation;

use League\Fractal\TransformerAbstract;


class LatestMessageTransformer extends TransformerAbstract
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
    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'senderId' => $data->senderId,
            'isRead' => $data->isRead,
            'conversationId' => $data->conversationId,
            'message' => $data->message,
            'type' => $data->type,
            "createdAt"=>strtotime($data->createdAt),
        ];
    }
}
