<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientTimeLogTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        if(empty($data)){
            return [];
        }
        return [
            'id'=>$data->udid,
            'categoryId'=>(!empty($data->category))?$data->categoryId:'',
            'category'=>(!empty($data->categoryName))?$data->categoryName:(!empty($data->category))?$data->category->name:'',
            'loggedId'=>$data->loggedId,
            'loggedBy'=>@$data->logged->firstName.' '.@$data->logged->lastName,
            'performedId'=>$data->performedId,
            'performedBy'=>(!empty(@$data->performedBy))?@$data->performedBy:@$data->performed->firstName.' '.@$data->performed->lastName,
            'date'=>strtotime($data->date),
            'timeAmount'=>$data->timeAmount,
            'patient'=>(!empty(@$data->patientName))?@$data->patientname:@$data->patient->firstName.' '.@$data->patient->middleName.' '.@$data->patient->lastName,
            'patientId'=>@$data->patient->udid,
            'staff'=>@$data->performed->firstName.' '.@$data->performed->lastName,
            'staffId'=>@$data->performed->udid,
            'note'=>(!empty($data->notes->note))?$data->notes->note:'',
            'noteId'=>(!empty($data->notes->id))?$data->notes->id:'',
            'cptCodeId'=>(!empty($data->cptCode))?$data->cptCode->udid:'',
            'cptCode'=>(!empty($data->cptCode))?$data->cptCode->name:'',
            'cptCodeDetail'=>(!empty($data->cptCode))?$data->cptCode->cptCode->name:'',
            'billingAmount'=>(!empty($data->cptCode))?$data->cptCode->billingAmout:'',
            'flagName'=>(!empty($data->notes->flag))?$data->notes->flag->name:'',
            'flagColor'=>(!empty($data->notes->flag))?$data->notes->flag->color:'',
            'flagId'=>(!empty($data->notes->flag))?$data->notes->flag->udid:'',
        ];
    }
}
