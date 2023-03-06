<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;


class StaffSpecializationTransformer extends TransformerAbstract
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
        return [
            'text'=>$data->specialization->name,
            'count'=>$data['count'],
		];
    }
}
