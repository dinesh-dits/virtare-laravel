<?php

namespace App\Transformers\AuditTimeLog;

use League\Fractal\TransformerAbstract;


class AuditTimeLogTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'timeAmount'=>$data->timeAmount,
            'createdAt'=>strtotime($data->createdAt),
            'note'=>$data->note,
            'flagId'=>(!empty($data->flag))?$data->flagId:'',
            'flag'=>(!empty($data->flag))?$data->flag->name:'',
            'color'=>(!empty($data->flag))?$data->flag->color:'',
            'createdById'=>$data->user->staff->udid,
            'cptCodeId'=>($data->cptCodeId)?$data->cptCodeId:'',
            'cptCode'=>($data->cptCode)?$data->cptCode->name:'',
            'createdBy'=>str_replace("  ", " ", ucfirst($data->user->staff->lastName) . ',' . ' ' . ucfirst($data->user->staff->firstName). ' ' . ucfirst($data->user->staff->middleName))
        ];
    }
}
