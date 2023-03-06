<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;

class PatientTaskGlobalCodeTransformer extends TransformerAbstract
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
           'id'=> $data->id,
           'name'=>$data->name,
           'description'=>$data->description,
           'isActive'=>$data->isActive,
        ];
      
    }
}
