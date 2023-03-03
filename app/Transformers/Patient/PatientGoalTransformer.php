<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientGoalTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id'=>$data->udid,
            'lowValue'=>$data->lowValue,
            'highValue'=>$data->highValue,
            'deviceTypeId'=>$data->deviceTypeId,
            'deviceType'=>$data->deviceType->name,
            'frequency'=>$data->frequency,
            'frequencyTypeId'=>$data->frequencyTypeId,
            'frequencyType'=>$data->frequencyType->name,
            'startDate'=>strtotime($data->startDate),
            'endDate'=>strtotime($data->endDate),
            'patientId'=>$data->patientId,
            'patientName'=>@$data->patient->firstName.' '.@$data->patient->lastName,
            'vitalFieldId'=>$data->vitalFieldId,
            'vitalField'=>$data->vitalField->name,
            'note'=>@$data->notes->note,
            'flagName'=>@$data->notes->flag->name,
            'flagColor'=>@$data->notes->flag->color
        ];
    }
}
