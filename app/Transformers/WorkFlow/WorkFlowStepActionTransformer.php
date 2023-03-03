<?php

namespace App\Transformers\WorkFlow;

use League\Fractal\TransformerAbstract;
 

class WorkFlowStepActionTransformer extends TransformerAbstract
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
            'workflowActionName'=>$data->actionName ,
            'workFlowActionId'=>$data->actionId ,
            'executionOffsetType'=>$data->executionOffsetType ,
            'executionOffsetDays'=>$data->executionOffsetDays ,
            'workFlowEventOffsetFieldId'=>$data->eventOffsetId ,
            'workFlowEventOffsetFieldName'=>$data->columnName ,
            'actionsField'=>(!empty($data->actionField))?json_decode($data->actionField):[]
        ];
      
    }
}
