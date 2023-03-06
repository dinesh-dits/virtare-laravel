<?php

namespace App\Transformers\Communication;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;


class CallRecordTransformer extends TransformerAbstract
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
          'staffId'=>$data->staff->id,
          'staff'=> str_replace("  ", " ", ucfirst($data->staff->lastName) . ',' . ' ' . ucfirst($data->staff->firstName). ' ' . ucfirst($data->staff->middleName)),
          'count'=>$data->count
		];
    }
}
