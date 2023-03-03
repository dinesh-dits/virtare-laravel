<?php

namespace App\Transformers\Escalation;

use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;


class EscalationTypeTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        
        return [
            'udid' => $data->udid,
            'escalationType' => $data->globalCodeName?$data->globalCodeName:"",
            'escalationTypeId' => $data->escalationTypeId,
            'escalationId' => $data->escalationId,
            'isActive' => $data->isActive?True:False,
        ];
    }
}
