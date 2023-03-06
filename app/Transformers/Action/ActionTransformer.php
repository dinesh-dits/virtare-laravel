<?php

namespace App\Transformers\Action;

use League\Fractal\TransformerAbstract;


class ActionTransformer extends TransformerAbstract
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
                'id' => $data->id,
			    'name'=>$data->name,
                'controller'=>$data->controller,
                'function' => $data->function,
		];
    }
}
