<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;

class EscalationAuditDescriptionTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'description'=>$data->description,
        ];
    }
}
