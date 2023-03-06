<?php

namespace App\Transformers\Staff;
use App\Transformers\Role\RoleTransformer;

use League\Fractal\TransformerAbstract;


class StaffRoleTransformer extends TransformerAbstract
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
            'id'=>$data->udid,
            "role" => fractal()->item($data->roles)->transformWith(new RoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
		];
    }
}
