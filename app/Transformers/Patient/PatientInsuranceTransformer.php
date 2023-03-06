<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\Inventory\InventoryTransformer;

class PatientInsuranceTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'insuranceNumber' => $data->insuranceNumber,
            'expirationDate' => $data->expirationDate,
            'patientId' => $data->patientId,
            'insuranceName' => $data->insuranceName->name,
            'insuranceNameId' => $data->insuranceName->id,
            'insuranceType' => $data->insuranceType->name,
            'insuranceTypeId' => $data->insuranceType->id,
        ];
    }
}
