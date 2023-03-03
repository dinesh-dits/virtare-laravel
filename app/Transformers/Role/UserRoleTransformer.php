<?php

namespace App\Transformers\Role;

use League\Fractal\TransformerAbstract;
 

class UserRoleTransformer extends TransformerAbstract
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
            'id'=>@$data->roles->udid,
            'name' =>@$data->roles->roles,
        ];
      
    }
}
