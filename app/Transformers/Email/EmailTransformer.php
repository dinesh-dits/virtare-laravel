<?php

namespace App\Transformers\Email;

use League\Fractal\TransformerAbstract;


class EmailTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'subject' => @$data->subject,
            //'message' => @$data->communicationMessage->message,
            'message' => $data->communicationMessage ? fractal()->collection($data->communicationMessage)->transformWith(new EmailMessageTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
            'communicationType' => $data->type->name,
            'senderId' => $data->from,
            'sender' => $data->sender->staff ? $data->sender->staff->firstName . ' ' . $data->sender->staff->lastName : $data->sender->patient->firstName . ' ' . $data->sender->patient->lastName,
            'receiverId' => $data->referenceId,
            'receiver' => $data->receiver->staff ? $data->receiver->staff->firstName . ' ' . $data->receiver->staff->lastName : $data->receiver->patient->firstName . ' ' . $data->receiver->patient->lastName,
        ];
    }
}
