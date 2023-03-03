<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;
use App\Transformers\Group\GroupTransformer;


class GroupStaffTransformer extends TransformerAbstract
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
    public function transform($data): array
    {
        return $data->group ? fractal()->item($data->group)->transformWith(new GroupTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array();
    }
}
