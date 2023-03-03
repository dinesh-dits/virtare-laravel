<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffDetailTransformer;
use App\Transformers\Referral\ReferralTransformer;

class EscalationAssignToTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        if ($data->entityType != 'referral') {

            return [
                'id' => $data->udid,
                'entityType' => $data->entityType,
                'staff' => $data->staff ? fractal()->item($data->staff)->transformWith(new StaffDetailTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            ];
        } else {
            return [
                'id' => $data->udid,
                'entityType' => "specialist",
                'staff' => $data->reffral ? fractal()->item($data->reffral)->transformWith(new ReferralTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            ];
        }
    }
}
