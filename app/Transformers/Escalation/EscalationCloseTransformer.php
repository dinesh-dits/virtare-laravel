<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;

class EscalationCloseTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'status'=>$data->status,
            'description'=>$data->description,
            'date' => strtotime($data->createdAt),
        ];
    }
}
