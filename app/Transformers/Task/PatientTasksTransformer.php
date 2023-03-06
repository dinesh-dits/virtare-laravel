<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;

class PatientTasksTransformer extends TransformerAbstract
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
           'id'=> $data->udid,
           'firstName' => $data->firstName,
           'lastName' => $data->lastName,
           'dob' => $data->dob ,
        ];
      
    }
}
