<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;

class TaskAssignedToTransformer extends TransformerAbstract
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
         return  $data->task ? fractal()->item($data->task)->transformWith(new TaskTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():""; 
    }
}
