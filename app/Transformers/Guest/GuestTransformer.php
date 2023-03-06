<?php

namespace App\Transformers\Guest;

use League\Fractal\TransformerAbstract;

class GuestTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        if(empty($data)){
            return [];
        }
        return [
            'id' => $data->udid,
            'sipId' => "UR0".$data->id,
            'conferenceId'=>$data->conferenceId,
            'name'=>$data->name,
            'email' => $data->email,
        ];
    }
}
