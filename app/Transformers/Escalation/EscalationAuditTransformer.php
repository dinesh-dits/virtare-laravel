<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffDetailTransformer;

class EscalationAuditTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'escalationId'=>$data->escalation->udid,
            'entityType'=>$data->entityType,
            'createdAt'=>strtotime($data->createdAt),
            'createdBy'=>$data->user?fractal()->item($data->user->staff)->transformWith(new StaffDetailTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
        ];
    }
}
