<?php

namespace App\Transformers\Condition;

use League\Fractal\TransformerAbstract;

class ConditionTransformer extends TransformerAbstract
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
          'id'=>$data->id,
          'code'=>$data->code,
          'description'=>$data->description,
        ];
    }
}
