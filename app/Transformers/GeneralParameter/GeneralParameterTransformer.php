<?php

namespace App\Transformers\GeneralParameter;

use League\Fractal\TransformerAbstract;

class GeneralParameterTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    { 
        return [
            'id'=>$data->udid,
            'generalParameterGroup'=>$data->generalParameterGroup->name,
            'vitalFieldName'=>$data->vitalField->name,
            'vitalFieldId'=>$data->vitalField->id,
            'highLimit'=>(!empty($data->highLimit))?$data->highLimit:'',
            'lowLimit'=>(!empty($data->lowLimit))?$data->lowLimit:''
        ];
    }
}
