<?php

namespace App\Transformers\CustomForms;

use Illuminate\Support\Str;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;


class CustomFormDetailTransformer extends TransformerAbstract
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
            'id' => $data['udid'],
            'order' => $data['order'],
            'name' => $data['name'],
            'type'=> $data['type'],  
            'required'=> $data['required'],   
            'properties'=> json_decode($data['properties']),   
        ];
    }
}
