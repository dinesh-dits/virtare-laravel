<?php

namespace App\Transformers\NonCompliance;

use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientDetailTransformer;
use App\Transformers\Patient\PatientInventoryTransformer;

class NonComplianceTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'udid' => $data->udid,
            'patient' => $data->patient ? fractal()->item($data->patient)->transformWith(new PatientDetailTransformer())->toArray() : '',
            'patientInventory' => $data->patientInventory ? fractal()->item($data->patientInventory)->transformWith(new PatientInventoryTransformer())->toArray() : '',
            'createdAt' => strtotime($data->createdAt),
        ];
    }
}
