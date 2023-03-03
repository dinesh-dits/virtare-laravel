<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;

class TaskDurationCountTransformer extends TransformerAbstract
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
           'total'=>$data->total,
           'duration'=>strtotime($data->duration),
           'time'=>$data->time,
           'color'=>$data->color,
           'text'=>$data->text
        ];
      
    }
}
