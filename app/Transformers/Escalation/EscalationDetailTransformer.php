<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientDetailTransformer;


class EscalationDetailTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'fromDate' => $data->fromDate?strtotime($data->fromDate):"",
            'toDate' => $data->toDate?strtotime($data->toDate):"",
            'entityType' => $data->entityType?$data->entityType:"",
            'value' => $data->value?$data->value:"",
        ];
    }
}
