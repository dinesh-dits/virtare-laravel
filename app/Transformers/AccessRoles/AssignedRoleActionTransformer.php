<?php

namespace App\Transformers\AccessRoles;

use League\Fractal\TransformerAbstract;


class AssignedRoleActionTransformer extends TransformerAbstract
{
   
    protected array $defaultIncludes = [
       
    ];
    
   
    protected array $availableIncludes = [
       
    ];
    
    
    public function transform($data): array
    {
        return [$data->actionId];
    }
}
