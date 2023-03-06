<?php

namespace App\Transformers\WorkFlow;

use League\Fractal\TransformerAbstract;
 

class WorkFlowTransformer extends TransformerAbstract
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
    public function transform($data)
    {
        return[ 
            'id'=> $data->udid ,
            'title'=>$data->workFlowTitle ,
            'description'=>$data->description ,
            'eventTitle'=>$data->eventTitle ,
            'startDate'=>strtotime($data->startDate) ,
            'endDate'=>strtotime($data->endDate) ,
            'eventId'=>$data->eventId ,
        ];
      
    }
}
