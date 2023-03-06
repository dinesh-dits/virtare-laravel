<?php

namespace App\Transformers\Appointment;

use League\Fractal\TransformerAbstract;

class AppoinmentCallStatusTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'callStatus' => $data->status ? $data->status->name : "",
            'description' => $data->status ? $data->status->description : "",
            'isActive' => $data->status ? $data->status->isActive : "",
        ];
    }
}
