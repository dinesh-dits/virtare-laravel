<?php

namespace App\Transformers\WorkFlow;

use League\Fractal\TransformerAbstract;
 

class WorkFlowEventColumnTransformer extends TransformerAbstract
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
            'title'=>$data->displayName ,
            'operator'=>json_decode($data->operator,true) ,
            'value'=>(!empty($data->valueSql))?\DB::select(
                            "CALL customSql('" . $data->valueSql . "');"
                        ):[],
        ];
      
    }
}
