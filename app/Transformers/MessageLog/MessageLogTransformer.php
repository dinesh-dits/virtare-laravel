<?php

namespace App\Transformers\MessageLog;

use League\Fractal\TransformerAbstract;


class MessageLogTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'from' => $data->udid,
            'to' => $data->userId,
            'subject' => $data->subject,
            'type' => $data->type,
            'status' => $data->status,
            'referenceId' => $data->referenceId,
            'isActive'  => $data->isActive ? True : False,
            'createdAt' => $data->createdAt
        ];
    }
}
