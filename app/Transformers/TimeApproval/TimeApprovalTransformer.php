<?php

namespace App\Transformers\TimeApproval;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffDetailTransformer;
use App\Transformers\Patient\PatientBasicInfoTransformer;

class TimeApprovalTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];
    protected array $availableIncludes = [];
    protected $showData;
    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'status' => (!empty($data->status))?$data->status->name:'',
            'statusId' => $data->statusId,
            'typeId' => $data->typeId,
            'type' => (!empty($data->type))?$data->type->name:'',
            'staff' => $this->showData && (!empty($data->staff))?fractal()->item($data->staff)->transformWith(new StaffDetailTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
            'patient' => $data->patient?fractal()->item($data->patient)->transformWith(new PatientBasicInfoTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
            'time' => $data->time,
            'createdAt' => strtotime($data->createdAt),
        ];
    }
}
