<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;


class TaskCategoryTransformer extends TransformerAbstract
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
           'id'=>(!empty($data->taskCategory))?$data->taskCategory->id:$data->taskCategoryId,
           'taskid'=>$data->taskId,
           'taskCategory'=>(!empty($data->taskCategory))?$data->taskCategory->name:$data->name,
        ];
      
    }
}
