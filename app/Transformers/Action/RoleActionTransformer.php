<?php

namespace App\Transformers\Action;

use League\Fractal\TransformerAbstract;


class RoleActionTransformer extends TransformerAbstract
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
			    'name'=>$data->name,
                'controller'=>$data->controller,
                'function' => $data->function,
		];
    }
}
