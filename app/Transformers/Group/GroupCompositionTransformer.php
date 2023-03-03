<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;


class GroupCompositionTransformer extends TransformerAbstract
{
   
    protected array $defaultIncludes = [
        //
    ];
    
   
    protected array $availableIncludes = [
        //
    ];
    
    
    public function transform($data): array
    {
        return [
            'id' =>$data->udid,
            'designationId' =>(!empty($data->designation))?$data->designation->id:'',
            'designation' =>(!empty($data->designation))?$data->designation->name:'',
            'count' =>$data->count,
		];
    }
}
