<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;
use App\Transformers\Group\GroupCompositionTransformer;


class GroupCompositionDetailTransformer extends TransformerAbstract
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
            'composition' =>(!empty($data))?fractal()->item($data)->transformWith(new GroupCompositionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            'patientCount' =>$data->group?$data->group->patientCount:0,
		];
    }
}
