<?php

namespace App\Transformers\Email;

use App\Models\User\User;
use League\Fractal\TransformerAbstract;


class EmailMessageTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $sender = User::where('id', $data->createdBy)->first();
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'message' => $data->message,
            'senderId' => $data->createdBy,
            'sender' => $sender->staff ? $sender->staff->firstName . ' ' . $sender->staff->lastName : $sender->patient->firstName . ' ' . $sender->patient->lastName,
            'time' => strtotime($data->createdAt),
        ];
    }
}
