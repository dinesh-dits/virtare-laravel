<?php

namespace App\Transformers\Conversation;

use League\Fractal\TransformerAbstract;


class ConversationTransformer extends TransformerAbstract
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
            'conversationId' => $data->communicationId,
            'senderId' => $data->senderId,
            'message' => $data->message,
            'type' => $data->type,
            'isRead' => $data->isRead,
            "createdAt"=>strtotime($data->createdAt),
        ];
    }
}
