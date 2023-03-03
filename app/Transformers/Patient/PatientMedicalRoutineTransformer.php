<?php

namespace App\Transformers\Patient;

use App\Transformers\GlobalCode\GlobalCodeTransformer;
use League\Fractal\TransformerAbstract;

class PatientMedicalRoutineTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'patientId' => $data->patientId,
            'medicine' => $data->medicine,
            'frequency'=>$data->medicalFrequency?fractal()->item($data->medicalFrequency)->transformWith(new GlobalCodeTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
            'startDate' =>strtotime($data->startDate),
            'endDate' => strtotime($data->endDate),
        ];
    }
}
