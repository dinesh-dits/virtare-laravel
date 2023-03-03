<?php

namespace App\Transformers\Communication;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;


class CallRecordListTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data): array
    {
        return [
          'staffId'=>$data->staff?$data->staff->udid:'',
          'patientId'=>$data->patient?$data->patient->udid:'',
          'staff'=>$data->staff? str_replace("  ", " ", ucfirst($data->staff->lastName) . ',' . ' ' . ucfirst($data->staff->firstName). ' ' . ucfirst($data->staff->middleName)):'',
          'patient'=>$data->patient? str_replace("  ", " ", ucfirst($data->patient->lastName) . ',' . ' ' . ucfirst($data->patient->firstName). ' ' . ucfirst($data->patient->middleName)):'',
          'date'=>strtotime($data->createdAt),
          'startTime'=>strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d",strtotime($data->createdAt))." ".$data->startTime))),
          'endTime'=>strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d",strtotime($data->createdAt))." ".$data->endTime))),
          'spendTime'=>$data->timeSpent
        ];
    }
}
